<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ManualDepositStatus;
use App\Enums\UserStatus;
use App\Enums\WalletTransactionType;
use App\Enums\WalletType;
use App\Models\AuditLog;
use App\Models\ManualDeposit;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\LedgerReconciliationService;
use App\Services\ManualDepositService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class ManualDepositTest extends TestCase
{
    use RefreshDatabase;

    private function investor(): User
    {
        return User::where('email', 'investor@forexbd.test')->firstOrFail();
    }

    private function creditManager(): User
    {
        return User::where('email', 'credit@forexbd.test')->firstOrFail();
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'amount_bdt' => '1200.0000',
            'payment_method' => 'bank_transfer',
            'proof_path' => 'deposits/1/11111111-1111-4111-8111-111111111111',
        ], $overrides);
    }

    public function test_user_can_submit_a_deposit_and_usd_is_computed_at_the_current_admin_rate(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        app(\App\Services\ExchangeRateService::class)->setRate($this->creditManager(), '120.0000', now());

        $response = $this->actingAs($investor)
            ->withHeader('Idempotency-Key', 'deposit-001')
            ->postJson('/api/v1/deposits', $this->payload([
                'proof_path' => 'deposits/'.$investor->id.'/11111111-1111-4111-8111-111111111111',
            ]));

        $response->assertCreated()
            ->assertJsonPath('data.amount_usd', '10.0000')
            ->assertJsonPath('data.amount_bdt', '1200.0000')
            ->assertJsonPath('data.status', ManualDepositStatus::Pending->value);

        $deposit = ManualDeposit::firstOrFail();
        $this->assertSame($investor->id, $deposit->user_id);
        $this->assertSame('deposit-001', $deposit->idempotency_key);
        $this->assertSame(0, WalletTransaction::where('reference_type', ManualDeposit::class)->count());
        $this->assertSame(0, Wallet::where('user_id', $investor->id)->count());
        $this->assertSame(1, AuditLog::where('action', 'deposit.submitted')->count());
    }

    public function test_duplicate_idempotency_key_returns_the_existing_deposit(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        app(\App\Services\ExchangeRateService::class)->setRate($this->creditManager(), '120.0000', now());

        $first = $this->actingAs($investor)->withHeader('Idempotency-Key', 'same-key')
            ->postJson('/api/v1/deposits', $this->payload([
                'proof_path' => 'deposits/'.$investor->id.'/11111111-1111-4111-8111-111111111111',
            ]));
        $second = $this->actingAs($investor)->withHeader('Idempotency-Key', 'same-key')
            ->postJson('/api/v1/deposits', $this->payload([
                'proof_path' => 'deposits/'.$investor->id.'/11111111-1111-4111-8111-111111111111',
            ]));

        $first->assertCreated();
        $second->assertOk()->assertJsonPath('data.id', $first->json('data.id'));
        $this->assertSame(1, ManualDeposit::count());
    }

    public function test_suspended_user_cannot_submit_a_deposit(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        $investor->status = UserStatus::Suspended;
        $investor->save();
        app(\App\Services\ExchangeRateService::class)->setRate($this->creditManager(), '120.0000', now());

        $this->actingAs($investor)->withHeader('Idempotency-Key', 'suspended')
            ->postJson('/api/v1/deposits', $this->payload([
                'proof_path' => 'deposits/'.$investor->id.'/11111111-1111-4111-8111-111111111111',
            ]))->assertForbidden();

        $this->assertSame(0, ManualDeposit::count());
    }

    public function test_submission_is_rejected_when_no_admin_rate_exists(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();

        $this->actingAs($investor)->withHeader('Idempotency-Key', 'no-rate')
            ->postJson('/api/v1/deposits', $this->payload([
                'proof_path' => 'deposits/'.$investor->id.'/11111111-1111-4111-8111-111111111111',
            ]))->assertUnprocessable();

        $this->assertSame(0, ManualDeposit::count());
    }

    public function test_credit_manager_can_view_the_deposit_queue(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs($this->creditManager())->get('/admin/manual-deposits')->assertOk();
    }

    public function test_support_cannot_view_the_deposit_queue(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs(User::where('email', 'support@forexbd.test')->firstOrFail())
            ->get('/admin/manual-deposits')->assertForbidden();
    }

    public function test_investor_cannot_view_the_deposit_queue(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs($this->investor())->get('/admin/manual-deposits')->assertForbidden();
    }

    public function test_approval_credits_usd_to_cash_wallet_with_an_immutable_ledger_row(): void
    {
        $this->seed(DatabaseSeeder::class);
        $deposit = ManualDeposit::factory()->create([
            'user_id' => $this->investor()->id,
            'amount_usd' => '100.0000',
            'amount_bdt' => '12000.0000',
        ]);

        app(ManualDepositService::class)->approve($this->creditManager(), $deposit);

        $deposit->refresh();
        $this->assertSame(ManualDepositStatus::Approved, $deposit->status);
        $this->assertSame($this->creditManager()->id, $deposit->reviewed_by);
        $this->assertNotNull($deposit->reviewed_at);
        $this->assertNotNull($deposit->wallet_transaction_id);

        $wallet = Wallet::where('user_id', $deposit->user_id)->where('type', WalletType::Cash)->firstOrFail();
        $this->assertSame('100.0000', $wallet->balance);
        $transaction = WalletTransaction::where('reference_type', ManualDeposit::class)
            ->where('reference_id', $deposit->id)
            ->firstOrFail();
        $this->assertSame(WalletTransactionType::Deposit, $transaction->type);
        $this->assertSame(ManualDeposit::class, $transaction->reference_type);
        $this->assertSame($deposit->id, $transaction->reference_id);
        $this->assertSame(0, app(LedgerReconciliationService::class)->reconcile()['breakCount']);
        $this->assertSame(1, AuditLog::where('action', 'deposit.approved')->count());
    }

    public function test_a_deposit_cannot_be_approved_twice(): void
    {
        $this->seed(DatabaseSeeder::class);
        $deposit = ManualDeposit::factory()->create(['user_id' => $this->investor()->id]);

        app(ManualDepositService::class)->approve($this->creditManager(), $deposit);

        $rejected = false;
        try {
            app(ManualDepositService::class)->approve($this->creditManager(), $deposit->fresh() ?? $deposit);
        } catch (RuntimeException) {
            $rejected = true;
        }

        $this->assertTrue($rejected);
        $this->assertSame(1, WalletTransaction::where('reference_type', ManualDeposit::class)->count());
        $this->assertSame('100.0000', Wallet::where('user_id', $deposit->user_id)->firstOrFail()->balance);
    }

    public function test_rejection_records_the_reason_and_reviewer_without_crediting(): void
    {
        $this->seed(DatabaseSeeder::class);
        $deposit = ManualDeposit::factory()->create(['user_id' => $this->investor()->id]);

        app(ManualDepositService::class)->reject($this->creditManager(), $deposit, 'Proof is unreadable.');

        $deposit->refresh();
        $this->assertSame(ManualDepositStatus::Rejected, $deposit->status);
        $this->assertSame('Proof is unreadable.', $deposit->rejection_reason);
        $this->assertSame($this->creditManager()->id, $deposit->reviewed_by);
        $this->assertNull($deposit->wallet_transaction_id);
        $this->assertSame(0, WalletTransaction::where('reference_type', ManualDeposit::class)->count());
        $this->assertSame(1, AuditLog::where('action', 'deposit.rejected')->count());
    }

    public function test_a_role_without_deposit_approval_permission_cannot_approve(): void
    {
        $this->seed(DatabaseSeeder::class);
        $deposit = ManualDeposit::factory()->create(['user_id' => $this->investor()->id]);
        $support = User::where('email', 'support@forexbd.test')->firstOrFail();

        $denied = false;
        try {
            app(ManualDepositService::class)->approve($support, $deposit);
        } catch (AuthorizationException) {
            $denied = true;
        }

        $this->assertTrue($denied);
        $this->assertSame(ManualDepositStatus::Pending, $deposit->fresh()?->status);
        $this->assertSame(0, WalletTransaction::where('reference_type', ManualDeposit::class)->count());
    }

    public function test_signed_r2_upload_url_is_generated_for_a_proof(): void
    {
        $this->seed(DatabaseSeeder::class);
        Storage::fake('r2');
        $investor = $this->investor();

        $response = $this->actingAs($investor)->postJson('/api/v1/deposits/proof-url');

        $response->assertOk()->assertJsonPath('data.method', 'PUT');
        $path = $response->json('data.path');
        $this->assertStringStartsWith('deposits/'.$investor->id.'/', (string) $path);
        $this->assertNotEmpty($response->json('data.url'));
    }
}
