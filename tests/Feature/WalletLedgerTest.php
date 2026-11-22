<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TransactionDirection;
use App\Enums\WalletTransactionType;
use App\Enums\WalletType;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Tests\TestCase;

class WalletLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_tables_have_expected_columns(): void
    {
        $walletColumns = ['id', 'user_id', 'type', 'currency', 'balance', 'created_at', 'updated_at'];
        foreach ($walletColumns as $column) {
            $this->assertTrue(Schema::hasColumn('wallets', $column), "Missing wallets.{$column}");
        }

        $ledgerColumns = [
            'id', 'uuid', 'wallet_id', 'direction', 'amount', 'balance_after', 'type',
            'reference_type', 'reference_id', 'counterparty_wallet_id', 'created_by', 'created_at',
        ];
        foreach ($ledgerColumns as $column) {
            $this->assertTrue(Schema::hasColumn('wallet_transactions', $column), "Missing wallet_transactions.{$column}");
        }

        $this->assertFalse(Schema::hasColumn('wallet_transactions', 'updated_at'));
    }

    public function test_money_columns_are_decimal_and_keep_four_places(): void
    {
        $wallet = Wallet::factory()->create(['balance' => '123.4567']);

        $this->assertSame('123.4567', $wallet->fresh()->balance);
    }

    public function test_wallet_type_is_unique_per_user(): void
    {
        $wallet = Wallet::factory()->create();

        $this->expectException(QueryException::class);

        Wallet::factory()->create([
            'user_id' => $wallet->user_id,
            'type' => $wallet->type,
        ]);
    }

    public function test_ledger_entries_cannot_be_updated_or_deleted(): void
    {
        $transaction = WalletTransaction::factory()->create(['amount' => '10.0000']);

        try {
            $transaction->reference_type = 'test';
            $transaction->save();
            $this->fail('Ledger update should have been blocked.');
        } catch (LogicException $exception) {
            $this->assertStringContainsString('reversal entry', $exception->getMessage());
        }

        $this->expectException(LogicException::class);
        $transaction->delete();
    }

    public function test_reconciliation_credits_minus_debits_equals_balance(): void
    {
        $wallet = Wallet::factory()->create(['balance' => '60.0000']);

        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'direction' => TransactionDirection::Credit,
            'amount' => '100.0000',
            'balance_after' => '100.0000',
            'type' => WalletTransactionType::Deposit,
        ]);

        WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'direction' => TransactionDirection::Debit,
            'amount' => '40.0000',
            'balance_after' => '60.0000',
            'type' => WalletTransactionType::Withdrawal,
        ]);

        $credit = WalletTransaction::query()->where('wallet_id', $wallet->id)->where('direction', 'credit')->firstOrFail();
        $debit = WalletTransaction::query()->where('wallet_id', $wallet->id)->where('direction', 'debit')->firstOrFail();

        $this->assertSame('100.0000', $credit->amount);
        $this->assertSame('40.0000', $debit->amount);
        $this->assertSame('60.0000', $wallet->fresh()->balance);
        $this->assertSame('60.0000', $debit->balance_after);
    }

    public function test_enums_match_schema_registry(): void
    {
        $this->assertSame(['cash', 'investment', 'profit', 'commission'], array_column(WalletType::cases(), 'value'));
        $this->assertSame(['debit', 'credit'], array_column(TransactionDirection::cases(), 'value'));
        $this->assertSame([
            'deposit', 'withdrawal', 'transfer_in', 'transfer_out', 'invest',
            'profit_distribution', 'loss_adjustment', 'commission', 'role_bonus',
            'capital_release', 'reversal', 'adjustment',
        ], array_column(WalletTransactionType::cases(), 'value'));
    }
}
