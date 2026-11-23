<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ExchangeRate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExchangeRate>
 */
class ExchangeRateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bdt_per_usd' => '118.0000',
            'effective_from' => '2026-11-23',
            'set_by' => User::factory(),
        ];
    }
}
