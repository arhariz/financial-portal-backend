<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\KycStatus;
use App\Enums\TransactionDirection;
use App\Enums\UserStatus;
use App\Enums\WalletType;
use App\Enums\WalletTransactionType;
use App\Enums\WithdrawalSourceWalletType;
use App\Enums\WithdrawalStatus;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Services\LedgerReconciliationService;
use App\Services\WalletService;
use App\Services\WithdrawalService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class WithdrawalTest extends TestCase
{
    use RefreshDatabase;

    private function investor(): User
    {
        $investor = User::where('email', 'investor@forexbd.test')->firstOrFail();
        $investor->kyc_status = KycStatus::Approved;
        $investor->status = UserStatus::Active;
        $investor->save();

        return $investor;
    }

    private function financeManager(): User
    {
        return User::where('email', 'finance@forexbd.test')->firstOrFail();
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'source_wallet_type' => WithdrawalSourceWalletType::Profit->value,
            'amount_usd' => '50.0000',
            'payout_method' => 'bank',
            'payout_details' => ['account' => 'BDT-123456'],
        ], $overrides);
    }

    private function pendingWithdrawal(User $investor, string $amount = '50.0000'): WithdrawalRequest
    {
        return WithdrawalRequest::factory()->create([
            'user_id' => $investor->id,
            'source_wallet_type' => WithdrawalSourceWalletType::Profit,
            'amount_usd' => $amount,
            'status' => WithdrawalStatus::Pending,
        ]);
    }

    public function test_kyc_approved_user_can_request_a_withdrawal(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();

        $response = $this->actingAs($investor)->withHeader('Idempotency-Key', 'wd-001')
            ->postJson('/api/v1/withdrawals', $this->payload());

        $response->assertCreated()
            ->assertJsonPath('data.status', WithdrawalStatus::Pending->value)
            ->assertJsonPath('data.amount_usd', '50.0000')
            ->assertJsonPath('data.source_wallet_type', WithdrawalSourceWalletType::Profit->value);

        $withdrawal = WithdrawalRequest::firstOrFail();
        $this->assertSame($investor->id, $withdrawal->user_id);
        $this->assertSame('wd-001', $withdrawal->idempotency_key);
        $this->assertSame(0, WalletTransaction::where('reference_type', WithdrawalRequest::class)->count());
        $this->assertSame(1, AuditLog::where('action', 'withdrawal.requested')->count());
    }

    public function test_duplicate_idempotency_key_returns_the_existing_request(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();

        $first = $this->actingAs($investor)->withHeader('Idempotency-Key', 'same-key')
            ->postJson('/api/v1/withdrawals', $this->payload());
        $second = $this->actingAs($investor)->withHeader('Idempotency-Key', 'same-key')
            ->postJson('/api/v1/withdrawals', $this->payload());

        $first->assertCreated();
        $second->assertOk()->assertJsonPath('data.id', $first->json('data.id'));
        $this->assertSame(1, WithdrawalRequest::count());
    }

    public function test_user_without_approved_kyc_cannot_request_a_withdrawal(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = User::where('email', 'investor@forexbd.test')->firstOrFail();
        $investor->kyc_status = KycStatus::Pending;
        $investor->save();

        $this->actingAs($investor)->withHeader('Idempotency-Key', 'pending-kyc')
            ->postJson('/api/v1/withdrawals', $this->payload())->assertForbidden();

        $this->assertSame(0, WithdrawalRequest::count());
    }

    public function test_suspended_user_cannot_request_a_withdrawal(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        $investor->status = UserStatus::Suspended;
        $investor->save();

        $this->actingAs($investor)->withHeader('Idempotency-Key', 'suspended')
            ->postJson('/api/v1/withdrawals', $this->payload())->assertForbidden();

        $this->assertSame(0, WithdrawalRequest::count());
    }

    public function test_finance_manager_can_view_the_withdrawal_queue(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs($this->financeManager())->get('/admin/withdrawals')->assertOk();
    }

    public function test_support_cannot_view_the_withdrawal_queue(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs(User::where('email', 'support@forexbd.test')->firstOrFail())
            ->get('/admin/withdrawals')->assertForbidden();
    }

    public function test_investor_cannot_view_the_withdrawal_queue(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs($this->investor())->get('/admin/withdrawals')->assertForbidden();
    }

    public function test_approval_records_the_reviewer_and_timestamp(): void
    {
        $this->seed(DatabaseSeeder::class);
        $withdrawal = $this->pendingWithdrawal($this->investor());

        app(WithdrawalService::class)->approve($this->financeManager(), $withdrawal);

        $withdrawal->refresh();
        $this->assertSame(WithdrawalStatus::Approved, $withdrawal->status);
        $this->assertSame($this->financeManager()->id, $withdrawal->approved_by);
        $this->assertNotNull($withdrawal->approved_at);
        $this->assertSame(1, AuditLog::where('action', 'withdrawal.approved')->count());
    }

    public function test_paying_an_approved_withdrawal_debits_the_profit_wallet_with_an_immutable_ledger_row(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        app(WalletService::class)->deposit($investor, WalletType::Profit, '100.0000');
        $withdrawal = $this->pendingWithdrawal($investor);
        app(WithdrawalService::class)->approve($this->financeManager(), $withdrawal);

        app(WithdrawalService::class)->markPaid($this->financeManager(), $withdrawal, 'Bank transfer reference 987');

        $withdrawal->refresh();
        $this->assertSame(WithdrawalStatus::Paid, $withdrawal->status);
        $this->assertNotNull($withdrawal->paid_at);
        $this->assertNotNull($withdrawal->wallet_transaction_id);

        $wallet = Wallet::where('user_id', $investor->id)->where('type', WalletType::Profit)->firstOrFail();
        $this->assertSame('50.0000', $wallet->balance);
        $transaction = WalletTransaction::where('reference_type', WithdrawalRequest::class)
            ->where('reference_id', $withdrawal->id)
            ->firstOrFail();
        $this->assertSame(TransactionDirection::Debit, $transaction->direction);
        $this->assertSame(WalletTransactionType::Withdrawal, $transaction->type);
        $this->assertSame('50.0000', $transaction->amount);
        $this->assertSame('50.0000', $transaction->balance_after);
        $this->assertSame(0, app(LedgerReconciliationService::class)->reconcile()['breakCount']);
        $this->assertSame(1, AuditLog::where('action', 'withdrawal.paid')->count());
    }

    public function test_withdrawal_cannot_be_paid_before_approval(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        app(WalletService::class)->deposit($investor, WalletType::Profit, '100.0000');
        $withdrawal = $this->pendingWithdrawal($investor);

        $rejected = false;
        try {
            app(WithdrawalService::class)->markPaid($this->financeManager(), $withdrawal, 'Premature');
        } catch (RuntimeException) {
            $rejected = true;
        }

        $this->assertTrue($rejected);
        $this->assertSame(WithdrawalStatus::Pending, $withdrawal->fresh()?->status);
        $this->assertSame(0, WalletTransaction::where('reference_type', WithdrawalRequest::class)->count());
    }

    public function test_withdrawal_cannot_be_paid_twice(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        app(WalletService::class)->deposit($investor, WalletType::Profit, '100.0000');
        $withdrawal = $this->pendingWithdrawal($investor);
        $service = app(WithdrawalService::class);
        $service->approve($this->financeManager(), $withdrawal);
        $service->markPaid($this->financeManager(), $withdrawal, 'First payment');

        $rejected = false;
        try {
            $service->markPaid($this->financeManager(), $withdrawal->fresh() ?? $withdrawal, 'Duplicate payment');
        } catch (RuntimeException) {
            $rejected = true;
        }

        $this->assertTrue($rejected);
        $this->assertSame(1, WalletTransaction::where('reference_type', WithdrawalRequest::class)->count());
        $this->assertSame('50.0000', Wallet::where('user_id', $investor->id)->firstOrFail()->balance);
    }

    public function test_insufficient_funds_block_payment_without_negative_balance(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        app(WalletService::class)->deposit($investor, WalletType::Profit, '20.0000');
        $withdrawal = $this->pendingWithdrawal($investor);
        $service = app(WithdrawalService::class);
        $service->approve($this->financeManager(), $withdrawal);

        $rejected = false;
        try {
            $service->markPaid($this->financeManager(), $withdrawal, 'Overdraft attempt');
        } catch (InvalidArgumentException) {
            $rejected = true;
        }

        $this->assertTrue($rejected);
        $this->assertSame(WithdrawalStatus::Approved, $withdrawal->fresh()?->status);
        $this->assertSame('20.0000', Wallet::where('user_id', $investor->id)->firstOrFail()->balance);
        $this->assertSame(0, WalletTransaction::where('reference_type', WithdrawalRequest::class)->count());
    }

    public function test_rejection_records_the_reason_without_debiting(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        app(WalletService::class)->deposit($investor, WalletType::Profit, '100.0000');
        $withdrawal = $this->pendingWithdrawal($investor);

        app(WithdrawalService::class)->reject($this->financeManager(), $withdrawal, 'Invalid payout details.');

        $withdrawal->refresh();
        $this->assertSame(WithdrawalStatus::Rejected, $withdrawal->status);
        $this->assertSame('Invalid payout details.', $withdrawal->rejection_reason);
        $this->assertSame($this->financeManager()->id, $withdrawal->approved_by);
        $this->assertNull($withdrawal->wallet_transaction_id);
        $this->assertSame('100.0000', Wallet::where('user_id', $investor->id)->firstOrFail()->balance);
        $this->assertSame(1, AuditLog::where('action', 'withdrawal.rejected')->count());
    }

    public function test_role_without_withdrawal_approval_permission_cannot_approve(): void
    {
        $this->seed(DatabaseSeeder::class);
        $withdrawal = $this->pendingWithdrawal($this->investor());
        $support = User::where('email', 'support@forexbd.test')->firstOrFail();

        $denied = false;
        try {
            app(WithdrawalService::class)->approve($support, $withdrawal);
        } catch (AuthorizationException) {
            $denied = true;
        }

        $this->assertTrue($denied);
        $this->assertSame(WithdrawalStatus::Pending, $withdrawal->fresh()?->status);
    }
}
