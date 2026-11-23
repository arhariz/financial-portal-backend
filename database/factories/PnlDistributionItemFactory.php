<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PnlDistribution;
use App\Models\PnlDistributionItem;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PnlDistributionItem>
 */
class PnlDistributionItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'distribution_id' => PnlDistribution::factory(),
            'position_id' => Position::factory(),
            'user_id' => User::factory(),
            'share_ratio' => '1.000000',
            'amount' => '100.0000',
            'wallet_transaction_id' => null,
        ];
    }
}
