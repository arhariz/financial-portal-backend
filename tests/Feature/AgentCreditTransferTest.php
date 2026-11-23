<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\KycStatus;
use App\Enums\TransactionDirection;
use App\Enums\UserStatus;
use App\Enums\WalletTransactionType;
use App\Enums\WalletType;
use App\Models\Agent;
use App\Models\AgentCreditTransfer;
use App\Models\AuditLog;
use App\Models\Commission;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\LedgerReconciliationService;
use App\Services\WalletService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentCreditTransferTest extends TestCase
{
    use RefreshDatabase;

    private function agentUser(): User
    {
        $user = User::where('email', 'agent1@forexbd.test')->firstOrFail();
        $user->kyc_status = KycStatus::Approved;
        $user->status = UserStatus::Active;
        $user->save();

        return $user;
    }

    private function investor(): User
    {
        $user = User::where('email', 'investor@forexbd.test')->firstOrFail();
        $user->kyc_status = KycStatus::Approved;
        $user->status = UserStatus::Active;
        $user->save();

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(User $investor): array
    {
        return [
            'investor_id' => $investor->id,
            'amount_usd' => '25.0000',
        ];
    }

    public function test_agent_transfer_credits_investor_and_debits_agent_with_two_immutable_rows(): void
    {
        $this->seed(DatabaseSeeder::class);
        $agentUser = $this->agentUser();
        $investor = $this->investor();
        Agent::factory()->create(['user_id' => $agentUser->id]);
        app(WalletService::class)->deposit($agentUser, WalletType::Cash, '100.0000');

        $response = $this->actingAs($agentUser)->withHeader('Idempotency-Key', 'tfr-001')
            ->postJson('/api/v1/agent-credit-transfers', $this->payload($investor));

        $response->assertCreated()
            ->assertJsonPath('data.amount_usd', '25.0000')
            ->assertJsonPath('data.investor_id', $investor->id);

        $transfer = AgentCreditTransfer::firstOrFail();
        $this->assertSame($agentUser->id, $transfer->agent_id);
        $this->assertSame('tfr-001', $transfer->idempotency_key);

        $agentCash = Wallet::where('user_id', $agentUser->id)->where('type', WalletType::Cash)->firstOrFail();
        $investorCash = Wallet::where('user_id', $investor->id)->where('type', WalletType::Cash)->firstOrFail();
        $this->assertSame('75.0000', $agentCash->balance);
        $this->assertSame('25.0000', $investorCash->balance);

        $rows = WalletTransaction::where('reference_type', AgentCreditTransfer::class)->get();
        $this->assertCount(2, $rows);
        $this->assertSame(WalletTransactionType::TransferOut, $rows->firstWhere('direction', TransactionDirection::Debit)->type);
        $this->assertSame(WalletTransactionType::TransferIn, $rows->firstWhere('direction', TransactionDirection::Credit)->type);
        $this->assertSame($agentCash->id, $rows->firstWhere('direction', TransactionDirection::Debit)?->wallet_id);
        $this->assertSame($investorCash->id, $rows->firstWhere('direction', TransactionDirection::Credit)?->wallet_id);
        $this->assertSame(0, app(LedgerReconciliationService::class)->reconcile()['breakCount']);
        $this->assertSame(1, AuditLog::where('action', 'agent_credit.transferred')->count());
    }

    public function test_transfer_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $agentUser = $this->agentUser();
        $investor = $this->investor();
        Agent::factory()->create(['user_id' => $agentUser->id]);
        app(WalletService::class)->deposit($agentUser, WalletType::Cash, '100.0000');

        $first = $this->actingAs($agentUser)->withHeader('Idempotency-Key', 'same-key')
            ->postJson('/api/v1/agent-credit-transfers', $this->payload($investor));
        $second = $this->actingAs($agentUser)->withHeader('Idempotency-Key', 'same-key')
            ->postJson('/api/v1/agent-credit-transfers', $this->payload($investor));

        $first->assertCreated();
        $second->assertOk()->assertJsonPath('data.id', $first->json('data.id'));
        $this->assertSame(1, AgentCreditTransfer::count());
        $this->assertSame(2, WalletTransaction::where('reference_type', AgentCreditTransfer::class)->count());
        $this->assertSame('75.0000', Wallet::where('user_id', $agentUser->id)->where('type', WalletType::Cash)->firstOrFail()->balance);
    }

    public function test_insufficient_agent_balance_blocks_the_transfer(): void
    {
        $this->seed(DatabaseSeeder::class);
        $agentUser = $this->agentUser();
        $investor = $this->investor();
        Agent::factory()->create(['user_id' => $agentUser->id]);
        app(WalletService::class)->deposit($agentUser, WalletType::Cash, '10.0000');

        $this->actingAs($agentUser)->withHeader('Idempotency-Key', 'tfr-poor')
            ->postJson('/api/v1/agent-credit-transfers', $this->payload($investor))
            ->assertUnprocessable();

        $this->assertSame(0, AgentCreditTransfer::count());
        $this->assertSame('10.0000', Wallet::where('user_id', $agentUser->id)->where('type', WalletType::Cash)->firstOrFail()->balance);
        $this->assertSame(0, WalletTransaction::where('reference_type', AgentCreditTransfer::class)->count());
    }

    public function test_non_agent_cannot_transfer_credit(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        $other = User::where('email', 'investor2@forexbd.test')->firstOrFail();
        $other->kyc_status = KycStatus::Approved;
        $other->status = UserStatus::Active;
        $other->save();

        $this->actingAs($other)->withHeader('Idempotency-Key', 'tfr-not-agent')
            ->postJson('/api/v1/agent-credit-transfers', $this->payload($investor))
            ->assertForbidden();

        $this->assertSame(0, AgentCreditTransfer::count());
    }

    public function test_suspended_agent_cannot_transfer(): void
    {
        $this->seed(DatabaseSeeder::class);
        $agentUser = $this->agentUser();
        $agentUser->status = UserStatus::Suspended;
        $agentUser->save();
        Agent::factory()->create(['user_id' => $agentUser->id]);
        app(WalletService::class)->deposit($agentUser, WalletType::Cash, '100.0000');

        $this->actingAs($agentUser)->withHeader('Idempotency-Key', 'tfr-suspended')
            ->postJson('/api/v1/agent-credit-transfers', $this->payload($this->investor()))
            ->assertForbidden();

        $this->assertSame(0, AgentCreditTransfer::count());
    }

    public function test_investor_without_approved_kyc_can_still_receive_credit(): void
    {
        $this->seed(DatabaseSeeder::class);
        $agentUser = $this->agentUser();
        $investor = User::where('email', 'investor@forexbd.test')->firstOrFail();
        $investor->kyc_status = KycStatus::None;
        $investor->save();
        Agent::factory()->create(['user_id' => $agentUser->id]);
        app(WalletService::class)->deposit($agentUser, WalletType::Cash, '100.0000');

        $this->actingAs($agentUser)->withHeader('Idempotency-Key', 'tfr-pending-kyc')
            ->postJson('/api/v1/agent-credit-transfers', $this->payload($investor))
            ->assertCreated();

        $this->assertSame('25.0000', Wallet::where('user_id', $investor->id)->where('type', WalletType::Cash)->firstOrFail()->balance);
    }

    public function test_suspended_investor_cannot_receive_credit(): void
    {
        $this->seed(DatabaseSeeder::class);
        $agentUser = $this->agentUser();
        $investor = $this->investor();
        $investor->status = UserStatus::Suspended;
        $investor->save();
        Agent::factory()->create(['user_id' => $agentUser->id]);
        app(WalletService::class)->deposit($agentUser, WalletType::Cash, '100.0000');

        $this->actingAs($agentUser)->withHeader('Idempotency-Key', 'tfr-suspended-investor')
            ->postJson('/api/v1/agent-credit-transfers', $this->payload($investor))
            ->assertForbidden();

        $this->assertSame(0, AgentCreditTransfer::count());
    }

    public function test_transfer_creates_no_mlm_commission_or_role_bonus(): void
    {
        $this->seed(DatabaseSeeder::class);
        $agentUser = $this->agentUser();
        $upline = Agent::factory()->create(['user_id' => User::factory()->create()->id]);
        Agent::factory()->create(['user_id' => $agentUser->id, 'upline_agent_id' => $upline->id]);
        app(WalletService::class)->deposit($agentUser, WalletType::Cash, '100.0000');

        $this->actingAs($agentUser)->withHeader('Idempotency-Key', 'tfr-isolation')
            ->postJson('/api/v1/agent-credit-transfers', $this->payload($this->investor()))
            ->assertCreated();

        $this->assertSame(0, Commission::count());
        $this->assertSame(0, WalletTransaction::where('type', WalletTransactionType::RoleBonus)->count());
    }

    public function test_credit_manager_can_view_the_transfer_queue(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs(User::where('email', 'credit@forexbd.test')->firstOrFail())
            ->get('/admin/agent-credit-transfers')->assertOk();
    }

    public function test_unauthenticated_transfer_is_rejected(): void
    {
        $this->postJson('/api/v1/agent-credit-transfers', [
            'investor_id' => 1,
            'amount_usd' => '25.0000',
        ])->assertUnauthorized();
    }
}
