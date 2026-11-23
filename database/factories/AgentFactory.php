<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserStatus;
use App\Models\Agent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agent>
 */
class AgentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'referral_code' => strtoupper(fake()->unique()->lexify('AG?????')),
            'tier' => 1,
            'upline_agent_id' => null,
            'status' => UserStatus::Active,
            'total_sales' => '0.0000',
            'active_referrals_count' => 0,
        ];
    }
}
