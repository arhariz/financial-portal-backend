<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Agent;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Referral>
 */
class ReferralFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agent_id' => Agent::factory(),
            'referred_user_id' => User::factory(),
            'level' => 1,
        ];
    }
}
