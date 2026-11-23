<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TransactionDirection;
use App\Enums\WalletType;
use App\Filament\Widgets\LedgerHealth;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\LedgerReconciliationService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LedgerHealthSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LedgerHealthWidgetTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateAsAdmin(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->actingAs(User::factory()->create()->assignRole('super_admin'));
    }

    public function test_green_when_every_wallet_reconciles(): void
    {
        $this->authenticateAsAdmin();

        $wallet = Wallet::factory()->create(['balance' => '100.0000']);
        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'direction' => TransactionDirection::Credit,
            'amount' => '100.0000',
            'balance_after' => '100.0000',
        ]);

        $widget = Livewire::test(LedgerHealth::class);

        $widget->assertSee('GREEN')->assertSee('Wallets checked: 1')->assertSee('Breaks: 0');
        $widget->assertSee('Finance Manager');
    }

    public function test_amber_for_a_small_break_in_the_investment_aggregate(): void
    {
        $this->authenticateAsAdmin();

        $wallet = Wallet::factory()->create(['type' => WalletType::Investment, 'balance' => '100.0000']);
        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'direction' => TransactionDirection::Credit,
            'amount' => '100.5000',
            'balance_after' => '100.5000',
        ]);

        $widget = Livewire::test(LedgerHealth::class);

        $widget->assertSee('AMBER');
    }

    public function test_red_for_a_break_in_a_money_wallet(): void
    {
        $this->authenticateAsAdmin();

        $wallet = Wallet::factory()->create(['type' => WalletType::Cash, 'balance' => '100.0000']);
        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'direction' => TransactionDirection::Credit,
            'amount' => '100.0001',
            'balance_after' => '100.0001',
        ]);

        $widget = Livewire::test(LedgerHealth::class);

        $widget->assertSee('RED');
    }

    public function test_state_is_read_from_ledger_data_not_hardcoded(): void
    {
        $this->authenticateAsAdmin();

        $wallet = Wallet::factory()->create(['balance' => '50.0000']);
        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'direction' => TransactionDirection::Credit,
            'amount' => '50.0000',
            'balance_after' => '50.0000',
        ]);

        Livewire::test(LedgerHealth::class)->assertSee('GREEN');

        Wallet::where('id', $wallet->id)->update(['balance' => '49.0000']);

        Livewire::test(LedgerHealth::class)->assertSee('RED');
    }

    public function test_view_permission_follows_reports_view(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs(User::where('email', 'support@forexbd.test')->firstOrFail());
        $this->assertTrue(LedgerHealth::canView());

        $this->actingAs(User::where('email', 'investor@forexbd.test')->firstOrFail());
        $this->assertFalse(LedgerHealth::canView());
    }

    public function test_drill_down_shows_the_wallets_ledger_rows(): void
    {
        $this->seed(DatabaseSeeder::class);

        $wallet = Wallet::factory()->create(['balance' => '10.0000']);
        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'direction' => TransactionDirection::Credit,
            'amount' => '10.0000',
            'balance_after' => '10.0000',
        ]);

        $this->actingAs(User::where('email', 'support@forexbd.test')->firstOrFail())
            ->get('/admin/reports?tab=ledger_wallets&wallet='.$wallet->id)
            ->assertOk()
            ->assertSee('Ledger rows for wallet '.$wallet->id)
            ->assertSee('adjustment');
    }

    public function test_sample_data_seeder_produces_a_reconciled_ledger(): void
    {
        $this->seed(LedgerHealthSeeder::class);
        $this->seed(LedgerHealthSeeder::class);

        $this->assertSame(1, Wallet::count());
        $this->assertSame(1, WalletTransaction::count());
        $this->assertSame(0, app(LedgerReconciliationService::class)->reconcile()['breakCount']);
    }
}
