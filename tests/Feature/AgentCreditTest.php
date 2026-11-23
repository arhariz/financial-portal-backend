<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\KycStatus;
use App\Enums\UserStatus;
use App\Enums\WalletTransactionType;
use App\Enums\WalletType;
use App\Models\Agent;
use App\Models\AgentCreditBonusRule;
use App\Models\AgentCreditPurchase;
use App\Models\AuditLog;
use App\Models\Commission;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\ExchangeRateService;
use App\Services\LedgerReconciliationService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentCreditTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::where('email', 'credit@forexbd.test')->firstOrFail();
    }

    private function agentUser(): User
    {
        $user = User::where('email', 'agent1@forexbd.test')->firstOrFail();
        $user->kyc_status = KycStatus::Approved;
        $user->status = UserStatus::Active;
        $user->save();

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(User $agent): array
    {
        return [
            'agent_id' => $agent->id,
            'amount_bdt' => '1200.0000',
        ];
    }

    public function test_admin_purchase_credits_face_value_and_configured_tier_bonus_as_two_immutable_rows(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = $this->admin();
        $agentUser = $this->agentUser();
        Agent::factory()->create(['user_id' => $agentUser->id, 'tier' => 1]);
        AgentCreditBonusRule::factory()->create(['tier' => 1, 'rate' => '0.500000']);
        app(ExchangeRateService::class)->setRate($admin, '120.0000', now());

        $response = $this->actingAs($admin)->withHeader('Idempotency-Key', 'ac-001')
            ->postJson('/api/v1/agent-credits', $this->payload($agentUser));

        $response->assertCreated()
            ->assertJsonPath('data.amount_usd', '10.0000')
            ->assertJsonPath('data.bonus_amount_usd', '5.0000');

        $purchase = AgentCreditPurchase::firstOrFail();
        $this->assertSame($agentUser->id, $purchase->user_id);
        $this->assertSame($admin->id, $purchase->created_by);
        $this->assertSame('ac-001', $purchase->idempotency_key);
        $this->assertSame(1, $purchase->agent_tier);
        $this->assertNotNull($purchase->bonus_rule_id);

        $cash = Wallet::where('user_id', $agentUser->id)->where('type', WalletType::Cash)->firstOrFail();
        $commission = Wallet::where('user_id', $agentUser->id)->where('type', WalletType::Commission)->firstOrFail();
        $this->assertSame('10.0000', $cash->balance);
        $this->assertSame('5.0000', $commission->balance);

        $rows = WalletTransaction::where('reference_type', AgentCreditPurchase::class)->get();
        $this->assertCount(2, $rows);
        $this->assertSame('10.0000', $rows->firstWhere('type', WalletTransactionType::Deposit)?->amount);
        $this->assertSame('5.0000', $rows->firstWhere('type', WalletTransactionType::RoleBonus)?->amount);
        $this->assertTrue($rows->every(fn (WalletTransaction $row): bool => $row->direction->value === 'credit'));
        $this->assertSame(0, app(LedgerReconciliationService::class)->reconcile()['breakCount']);
        $this->assertSame(1, AuditLog::where('action', 'agent_credit.purchased')->count());
    }

    public function test_no_bonus_rule_credits_face_value_only(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = $this->admin();
        $agentUser = $this->agentUser();
        Agent::factory()->create(['user_id' => $agentUser->id]);
        app(ExchangeRateService::class)->setRate($admin, '120.0000', now());

        $this->actingAs($admin)->withHeader('Idempotency-Key', 'ac-zero')
            ->postJson('/api/v1/agent-credits', $this->payload($agentUser))
            ->assertCreated()
            ->assertJsonPath('data.bonus_amount_usd', '0.0000');

        $rows = WalletTransaction::where('reference_type', AgentCreditPurchase::class)->get();
        $this->assertCount(1, $rows);
        $this->assertSame(WalletTransactionType::Deposit, $rows->first()->type);
    }

    public function test_face_value_uses_the_current_admin_rate_not_a_hardcoded_value(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = $this->admin();
        $agentUser = $this->agentUser();
        Agent::factory()->create(['user_id' => $agentUser->id]);
        $rates = app(ExchangeRateService::class);
        $rates->setRate($admin, '100.0000', now()->subDay());
        $rates->setRate($admin, '200.0000', now());

        $this->actingAs($admin)->withHeader('Idempotency-Key', 'ac-rate')
            ->postJson('/api/v1/agent-credits', $this->payload($agentUser))
            ->assertCreated()
            ->assertJsonPath('data.amount_usd', '6.0000');
    }

    public function test_role_bonus_never_creates_an_upline_commission(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = $this->admin();
        $agentUser = $this->agentUser();
        $upline = Agent::factory()->create(['user_id' => User::factory()->create()->id]);
        Agent::factory()->create(['user_id' => $agentUser->id, 'upline_agent_id' => $upline->id]);
        AgentCreditBonusRule::factory()->create(['tier' => 1, 'rate' => '0.500000']);
        app(ExchangeRateService::class)->setRate($admin, '120.0000', now());

        $this->actingAs($admin)->withHeader('Idempotency-Key', 'ac-upline')
            ->postJson('/api/v1/agent-credits', $this->payload($agentUser))
            ->assertCreated();

        $this->assertSame(0, Commission::count());
    }

    public function test_role_without_agent_credit_manage_cannot_purchase(): void
    {
        $this->seed(DatabaseSeeder::class);
        $agentUser = $this->agentUser();
        Agent::factory()->create(['user_id' => $agentUser->id]);
        $investor = User::where('email', 'investor@forexbd.test')->firstOrFail();

        $this->actingAs($investor)->withHeader('Idempotency-Key', 'ac-denied')
            ->postJson('/api/v1/agent-credits', $this->payload($agentUser))
            ->assertForbidden();

        $this->assertSame(0, AgentCreditPurchase::count());
    }

    public function test_support_role_is_view_only_for_agent_credits(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs(User::where('email', 'support@forexbd.test')->firstOrFail())
            ->postJson('/api/v1/agent-credits', $this->payload($this->agentUser()))
            ->assertForbidden();
    }

    public function test_credit_manager_can_view_the_agent_credit_queue(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs($this->admin())->get('/admin/agent-credits')->assertOk();
    }

    public function test_target_must_be_an_active_agent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = $this->admin();
        app(ExchangeRateService::class)->setRate($admin, '120.0000', now());

        $this->actingAs($admin)->withHeader('Idempotency-Key', 'ac-not-agent')
            ->postJson('/api/v1/agent-credits', $this->payload($this->agentUser()))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('agent_id');

        $this->assertSame(0, AgentCreditPurchase::count());
    }

    public function test_agent_without_approved_kyc_cannot_receive_credit(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = $this->admin();
        $agentUser = User::where('email', 'agent1@forexbd.test')->firstOrFail();
        $agentUser->kyc_status = KycStatus::Pending;
        $agentUser->save();
        Agent::factory()->create(['user_id' => $agentUser->id]);
        app(ExchangeRateService::class)->setRate($admin, '120.0000', now());

        $this->actingAs($admin)->withHeader('Idempotency-Key', 'ac-kyc')
            ->postJson('/api/v1/agent-credits', $this->payload($agentUser))
            ->assertForbidden();

        $this->assertSame(0, AgentCreditPurchase::count());
    }

    public function test_duplicate_idempotency_key_returns_the_existing_purchase_without_double_credit(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = $this->admin();
        $agentUser = $this->agentUser();
        Agent::factory()->create(['user_id' => $agentUser->id]);
        AgentCreditBonusRule::factory()->create(['tier' => 1, 'rate' => '0.500000']);
        app(ExchangeRateService::class)->setRate($admin, '120.0000', now());

        $first = $this->actingAs($admin)->withHeader('Idempotency-Key', 'same-key')
            ->postJson('/api/v1/agent-credits', $this->payload($agentUser));
        $second = $this->actingAs($admin)->withHeader('Idempotency-Key', 'same-key')
            ->postJson('/api/v1/agent-credits', $this->payload($agentUser));

        $first->assertCreated();
        $second->assertOk()->assertJsonPath('data.id', $first->json('data.id'));
        $this->assertSame(1, AgentCreditPurchase::count());
        $this->assertSame(2, WalletTransaction::where('reference_type', AgentCreditPurchase::class)->count());
        $this->assertSame('10.0000', Wallet::where('user_id', $agentUser->id)->where('type', WalletType::Cash)->firstOrFail()->balance);
    }

    public function test_bonus_rule_is_selected_by_agent_tier(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = $this->admin();
        $agentUser = $this->agentUser();
        Agent::factory()->create(['user_id' => $agentUser->id, 'tier' => 2]);
        AgentCreditBonusRule::factory()->create(['tier' => 1, 'rate' => '0.500000']);
        AgentCreditBonusRule::factory()->create(['tier' => 2, 'rate' => '0.100000']);
        app(ExchangeRateService::class)->setRate($admin, '120.0000', now());

        $this->actingAs($admin)->withHeader('Idempotency-Key', 'ac-tier')
            ->postJson('/api/v1/agent-credits', $this->payload($agentUser))
            ->assertCreated()
            ->assertJsonPath('data.agent_tier', 2)
            ->assertJsonPath('data.bonus_amount_usd', '1.0000');

        $this->assertSame('1.0000', Wallet::where('user_id', $agentUser->id)->where('type', WalletType::Commission)->firstOrFail()->balance);
    }

    public function test_inactive_bonus_rule_is_ignored(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = $this->admin();
        $agentUser = $this->agentUser();
        Agent::factory()->create(['user_id' => $agentUser->id, 'tier' => 1]);
        AgentCreditBonusRule::factory()->create(['tier' => 1, 'rate' => '0.500000', 'active' => false]);
        app(ExchangeRateService::class)->setRate($admin, '120.0000', now());

        $this->actingAs($admin)->withHeader('Idempotency-Key', 'ac-inactive')
            ->postJson('/api/v1/agent-credits', $this->payload($agentUser))
            ->assertCreated()
            ->assertJsonPath('data.bonus_amount_usd', '0.0000');

        $this->assertSame(1, WalletTransaction::where('reference_type', AgentCreditPurchase::class)->count());
    }

    public function test_bonus_rate_uses_decimal_precision(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = $this->admin();
        $agentUser = $this->agentUser();
        Agent::factory()->create(['user_id' => $agentUser->id, 'tier' => 3]);
        AgentCreditBonusRule::factory()->create(['tier' => 3, 'rate' => '0.333333']);
        app(ExchangeRateService::class)->setRate($admin, '120.0000', now());

        $this->actingAs($admin)->withHeader('Idempotency-Key', 'ac-precision')
            ->postJson('/api/v1/agent-credits', $this->payload($agentUser))
            ->assertCreated()
            ->assertJsonPath('data.bonus_amount_usd', '3.3333');
    }

    public function test_admin_can_manage_bonus_rules_but_credit_manager_cannot(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs(User::where('email', 'admin@forexbd.test')->firstOrFail())
            ->get('/admin/agent-credit-bonus-rules')->assertOk();
    }

    public function test_credit_manager_cannot_manage_bonus_rules(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs($this->admin())->get('/admin/agent-credit-bonus-rules')->assertForbidden();
    }

    public function test_no_admin_rate_blocks_purchase(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = $this->admin();
        $agentUser = $this->agentUser();
        Agent::factory()->create(['user_id' => $agentUser->id]);

        $this->actingAs($admin)->withHeader('Idempotency-Key', 'ac-no-rate')
            ->postJson('/api/v1/agent-credits', $this->payload($agentUser))
            ->assertUnprocessable();

        $this->assertSame(0, AgentCreditPurchase::count());
    }

    public function test_unauthenticated_purchase_is_rejected(): void
    {
        $this->postJson('/api/v1/agent-credits', [
            'agent_id' => 1,
            'amount_bdt' => '1200.0000',
        ])->assertUnauthorized();
    }
}
