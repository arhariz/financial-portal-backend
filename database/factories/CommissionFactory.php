<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CommissionEvent;
use App\Enums\CommissionStatus;
use App\Models\Agent;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Commission>
 */
class CommissionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agent_id' => Agent::factory(),
            'rule_id' => CommissionRule::factory(),
            'event' => CommissionEvent::Investment,
            'source_user_id' => User::factory(),
            'source_reference_type' => 'positions',
            'source_reference_id' => 1,
            'base_amount' => '100.0000',
            'amount' => '5.0000',
            'status' => CommissionStatus::Pending,
            'vest_at' => null,
            'wallet_transaction_id' => null,
        ];
    }
}
