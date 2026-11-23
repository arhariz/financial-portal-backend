<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TransactionDirection;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Command\Command;
use Tests\TestCase;

class LedgerReconcileCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_zero_breaks_when_credits_equal_balance(): void
    {
        $wallet = Wallet::factory()->create(['balance' => '100.0000']);
        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'direction' => TransactionDirection::Credit,
            'amount' => '100.0000',
            'balance_after' => '100.0000',
        ]);

        $exit = Artisan::call('ledger:reconcile');

        $this->assertSame(Command::SUCCESS, $exit);
        $this->assertStringContainsString('Breaks: 0', Artisan::output());
    }

    public function test_break_is_detected_and_command_fails(): void
    {
        $wallet = Wallet::factory()->create(['balance' => '90.0000']);
        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'direction' => TransactionDirection::Credit,
            'amount' => '100.0000',
            'balance_after' => '100.0000',
        ]);

        $exit = Artisan::call('ledger:reconcile');

        $output = Artisan::output();

        $this->assertSame(Command::FAILURE, $exit);
        $this->assertStringContainsString('BREAK', $output);
        $this->assertStringContainsString('Breaks: 1', $output);
    }

    public function test_debits_are_subtracted_from_the_expected_balance(): void
    {
        $wallet = Wallet::factory()->create(['balance' => '70.0000']);
        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'direction' => TransactionDirection::Credit,
            'amount' => '100.0000',
            'balance_after' => '100.0000',
        ]);
        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'direction' => TransactionDirection::Debit,
            'amount' => '30.0000',
            'balance_after' => '70.0000',
        ]);

        $this->assertSame(Command::SUCCESS, Artisan::call('ledger:reconcile'));
    }

    public function test_four_decimal_precision_has_no_float_drift(): void
    {
        $wallet = Wallet::factory()->create(['balance' => '3.0003']);
        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'direction' => TransactionDirection::Credit,
            'amount' => '1.0001',
            'balance_after' => '1.0001',
        ]);
        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'direction' => TransactionDirection::Credit,
            'amount' => '2.0002',
            'balance_after' => '3.0003',
        ]);

        $this->assertSame(Command::SUCCESS, Artisan::call('ledger:reconcile'));
    }

    public function test_json_output_carries_break_count_and_wallet_rows(): void
    {
        $wallet = Wallet::factory()->create(['balance' => '0.0000']);

        $exit = Artisan::call('ledger:reconcile', ['--json' => true]);
        $payload = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame(Command::SUCCESS, $exit);
        $this->assertSame(0, $payload['breakCount']);
        $this->assertCount(1, $payload['wallets']);
        $this->assertSame($wallet->id, $payload['wallets'][0]['id']);
        $this->assertSame('0.0000', $payload['wallets'][0]['difference']);
    }

    public function test_no_wallets_reports_zero_breaks(): void
    {
        $this->assertSame(Command::SUCCESS, Artisan::call('ledger:reconcile'));
        $this->assertStringContainsString('Breaks: 0', Artisan::output());
    }
}
