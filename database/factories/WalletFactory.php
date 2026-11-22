<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WalletType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Wallet>
 */
class WalletFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => WalletType::Cash,
            'currency' => 'USD',
            'balance' => '0.0000',
        ];
    }
}
