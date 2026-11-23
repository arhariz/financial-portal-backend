<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PnlDistributionStatus;
use App\Models\PnlDistribution;
use App\Models\Symbol;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PnlDistribution>
 */
class PnlDistributionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'symbol_id' => Symbol::factory(),
            'trade_date' => '2026-11-23',
            'profit_loss' => '100.0000',
            'entry_by' => null,
            'status' => PnlDistributionStatus::Draft,
            'distributed_at' => null,
            'reversal_of_id' => null,
        ];
    }
}
