<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AgentCreditTransfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgentCreditTransfer>
 */
class AgentCreditTransferFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agent_id' => User::factory(),
            'investor_id' => User::factory(),
            'amount_usd' => '25.0000',
            'idempotency_key' => null,
        ];
    }
}
