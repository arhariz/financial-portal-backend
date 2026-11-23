<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SymbolCategory;
use App\Enums\SymbolStatus;
use App\Models\Symbol;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Symbol>
 */
class SymbolFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('SYM???')),
            'name' => fake()->word(),
            'category' => SymbolCategory::Metal,
            'status' => SymbolStatus::Active,
            'min_investment' => '10.0000',
            'created_by' => User::factory(),
        ];
    }
}
