<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TransactionDirection;
use App\Enums\WalletTransactionType;
use App\Enums\WalletType;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\LedgerReconciliationService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_deposit_credits_the_cash_wallet_and_writes_a_ledger_row(): void
    {
        $user = User::factory()->create();
        $service = app(WalletService::class);

        $transaction = $service->deposit($user, WalletType::Cash, '100.0000');

        $this->assertSame('100.0000', $transaction->amount);
        $this->assertSame(TransactionDirection::Credit, $transaction->direction);
        $this->assertSame(WalletTransactionType::Deposit, $transaction->type);
        $this->assertSame('100.0000', $transaction->balance_after);

        $wallet = Wallet::where('user_id', $user->id)->where('type', WalletType::Cash)->firstOrFail();
        $this->assertSame('100.0000', $wallet->balance);
        $this->assertSame(1, WalletTransaction::count());

        $this->assertSame(0, app(LedgerReconciliationService::class)->reconcile()['breakCount']);
    }

    public function test_deposit_creates_the_wallet_when_it_does_not_exist(): void
    {
        $user = User::factory()->create();

        $this->assertSame(0, Wallet::where('user_id', $user->id)->count());

        app(WalletService::class)->deposit($user, WalletType::Profit, '25.0000');

        $this->assertSame(1, Wallet::where('user_id', $user->id)->where('type', WalletType::Profit)->count());
        $this->assertSame('25.0000', Wallet::where('user_id', $user->id)->firstOrFail()->balance);
    }

    public function test_deposit_preserves_four_decimal_precision(): void
    {
        $user = User::factory()->create();

        app(WalletService::class)->deposit($user, WalletType::Cash, '0.0001');

        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('0.0001', $wallet->balance);
        $this->assertSame('0.0001', WalletTransaction::firstOrFail()->amount);
    }

    public function test_repeated_deposits_accumulate(): void
    {
        $user = User::factory()->create();
        $service = app(WalletService::class);

        $service->deposit($user, WalletType::Cash, '50.0000');
        $service->deposit($user, WalletType::Cash, '50.0000');

        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('100.0000', $wallet->balance);
        $this->assertSame(2, WalletTransaction::count());
        $this->assertSame(0, app(LedgerReconciliationService::class)->reconcile()['breakCount']);
    }

    public function test_deposit_rejects_non_positive_amounts(): void
    {
        $user = User::factory()->create();
        $service = app(WalletService::class);

        foreach (['0.0000', '-5.0000'] as $amount) {
            $rejected = false;

            try {
                $service->deposit($user, WalletType::Cash, $amount);
            } catch (InvalidArgumentException) {
                $rejected = true;
            }

            $this->assertTrue($rejected, "Expected InvalidArgumentException for {$amount}");
        }

        $this->assertSame(0, WalletTransaction::count());
    }
}
