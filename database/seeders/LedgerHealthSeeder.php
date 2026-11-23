<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TransactionDirection;
use App\Enums\WalletTransactionType;
use App\Enums\WalletType;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Minimal reconciled wallet + ledger row so the Ledger Health widget has real data
 * to display (TST-13 / D-071). Idempotent; the comprehensive wallet fixtures stay in WP10-T04.
 */
class LedgerHealthSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'ledger-demo@forexbd.test'],
            ['name' => 'Ledger Demo', 'password' => Hash::make('Password@123')]
        );

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id, 'type' => WalletType::Cash],
            ['currency' => 'USD', 'balance' => '100.0000']
        );

        WalletTransaction::firstOrCreate(
            ['uuid' => 'ledger-health-seed-0001'],
            [
                'wallet_id' => $wallet->id,
                'direction' => TransactionDirection::Credit,
                'amount' => '100.0000',
                'balance_after' => '100.0000',
                'type' => WalletTransactionType::Deposit,
                'reference_type' => null,
                'reference_id' => null,
                'counterparty_wallet_id' => null,
                'created_by' => null,
            ]
        );
    }
}
