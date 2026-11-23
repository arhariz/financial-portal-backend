<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AgentCreditPurchase;
use App\Models\ExchangeRate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgentCreditPurchase>
 */
class AgentCreditPurchaseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount_bdt' => '1200.0000',
            'amount_usd' => '10.0000',
            'bonus_amount_usd' => '5.0000',
            'exchange_rate_id' => ExchangeRate::factory(),
            'idempotency_key' => null,
            'created_by' => null,
        ];
    }
}
