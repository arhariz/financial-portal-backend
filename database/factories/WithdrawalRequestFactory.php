<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WithdrawalSourceWalletType;
use App\Enums\WithdrawalStatus;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WithdrawalRequest>
 */
class WithdrawalRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'source_wallet_type' => WithdrawalSourceWalletType::Profit,
            'amount_usd' => '50.0000',
            'payout_method' => 'bank',
            'payout_details' => ['account' => 'sample-account'],
            'status' => WithdrawalStatus::Pending,
            'approved_by' => null,
            'approved_at' => null,
            'paid_at' => null,
            'rejection_reason' => null,
            'wallet_transaction_id' => null,
        ];
    }
}
