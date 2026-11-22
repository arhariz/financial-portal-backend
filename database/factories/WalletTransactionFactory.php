<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TransactionDirection;
use App\Enums\WalletTransactionType;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\WalletTransaction>
 */
class WalletTransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'wallet_id' => Wallet::factory(),
            'direction' => TransactionDirection::Credit,
            'amount' => '0.0000',
            'balance_after' => '0.0000',
            'type' => WalletTransactionType::Adjustment,
        ];
    }
}
