<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PositionSource;
use App\Enums\PositionStatus;
use App\Models\Position;
use App\Models\Symbol;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'symbol_id' => Symbol::factory(),
            'capital' => '100.0000',
            'original_amount' => '100.0000',
            'investment_date' => '2026-11-23',
            'eligible_date' => '2026-11-25',
            'status' => PositionStatus::Active,
            'source' => PositionSource::Cash,
        ];
    }
}
