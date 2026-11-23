<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ManualDepositStatus;
use App\Models\ExchangeRate;
use App\Models\ManualDeposit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ManualDeposit>
 */
class ManualDepositFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount_usd' => '100.0000',
            'amount_bdt' => '11800.0000',
            'exchange_rate_id' => ExchangeRate::factory(),
            'payment_method' => 'bank_transfer',
            'proof_path' => 'proofs/sample-deposit.txt',
            'status' => ManualDepositStatus::Pending,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'rejection_reason' => null,
            'wallet_transaction_id' => null,
        ];
    }
}
