<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TransactionDirection;
use App\Enums\WalletTransactionType;
use App\Enums\WalletType;
use App\Filament\Widgets\ApprovalQueues;
use App\Filament\Widgets\MoneyFlow;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class MoneyFlowWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function authenticatedUser(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('email', 'admin@forexbd.test')->firstOrFail());
    }

    public function test_money_flow_reports_live_aum_and_net_flow_from_the_ledger(): void
    {
        $this->authenticatedUser();
        Carbon::setTestNow(Carbon::parse('2026-11-23 12:00:00'));

        $wallet = Wallet::factory()->create(['type' => WalletType::Cash, 'balance' => '150.0000']);
        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'direction' => TransactionDirection::Credit,
            'type' => WalletTransactionType::Deposit,
            'amount' => '200.0000',
            'balance_after' => '200.0000',
            'created_at' => now()->subDay(),
        ]);
        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'direction' => TransactionDirection::Debit,
            'type' => WalletTransactionType::Withdrawal,
            'amount' => '50.0000',
            'balance_after' => '150.0000',
            'created_at' => now()->subDay(),
        ]);

        $widget = Livewire::test(MoneyFlow::class);

        $widget->assertSee('Money Flow')
            ->assertSee('AUM')
            ->assertSee('150.0000')
            ->assertSee('Net flow (30d)')
            ->assertSee('150.0000')
            ->assertSee('GREEN')
            ->assertSee('CFO / Admin');
    }

    public function test_money_flow_is_red_when_the_ledger_has_a_break(): void
    {
        $this->authenticatedUser();

        $wallet = Wallet::factory()->create(['type' => WalletType::Cash, 'balance' => '100.0000']);
        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'direction' => TransactionDirection::Credit,
            'type' => WalletTransactionType::Deposit,
            'amount' => '100.5000',
            'balance_after' => '100.5000',
        ]);

        Livewire::test(MoneyFlow::class)->assertSee('RED');
    }

    public function test_approval_queues_are_green_when_no_requests_are_pending(): void
    {
        $this->authenticatedUser();

        Livewire::test(ApprovalQueues::class)
            ->assertSee('Approval Queues')
            ->assertSee('Deposits pending')
            ->assertSee('Withdrawals pending')
            ->assertSee('Capital releases pending')
            ->assertSee('GREEN')
            ->assertSee('Credit Manager');
    }

    public function test_approval_queues_turn_amber_after_24_hours_and_red_after_48(): void
    {
        $this->authenticatedUser();
        Carbon::setTestNow(Carbon::parse('2026-11-23 12:00:00'));

        \App\Models\ManualDeposit::factory()->create([
            'status' => \App\Enums\ManualDepositStatus::Pending,
            'created_at' => now()->subHours(30),
        ]);

        Livewire::test(ApprovalQueues::class)->assertSee('AMBER');

        \App\Models\ManualDeposit::query()->update(['created_at' => now()->subHours(50)]);

        Livewire::test(ApprovalQueues::class)->assertSee('RED');
    }

    public function test_widget_states_are_read_from_live_data(): void
    {
        $this->authenticatedUser();
        Carbon::setTestNow(Carbon::parse('2026-11-23 12:00:00'));

        Livewire::test(ApprovalQueues::class)->assertSee('GREEN');

        \App\Models\WithdrawalRequest::factory()->create([
            'status' => \App\Enums\WithdrawalStatus::Pending,
            'requested_at' => now()->subHours(30),
        ]);

        Livewire::test(ApprovalQueues::class)->assertSee('AMBER');
    }

    public function test_widgets_follow_reports_view_permission(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs(User::where('email', 'support@forexbd.test')->firstOrFail());
        $this->assertTrue(MoneyFlow::canView());
        $this->assertTrue(ApprovalQueues::canView());

        $this->actingAs(User::where('email', 'investor@forexbd.test')->firstOrFail());
        $this->assertFalse(MoneyFlow::canView());
        $this->assertFalse(ApprovalQueues::canView());
    }

    public function test_reports_money_flow_tab_renders_the_widgets(): void
    {
        $this->authenticatedUser();

        $this->actingAs(User::where('email', 'admin@forexbd.test')->firstOrFail())
            ->get('/admin/reports?tab=money_flow')
            ->assertOk()
            ->assertSee('Money Flow')
            ->assertSee('Approval Queues');
    }
}
