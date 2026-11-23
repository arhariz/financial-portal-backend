<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CommissionEvent;
use App\Models\CommissionRule;
use App\Models\DeductionProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommissionRule>
 */
class CommissionRuleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event' => CommissionEvent::Investment,
            'agent_tier' => 1,
            'level' => 1,
            'rate' => '0.050000',
            'enabled' => true,
            'holding_period_hours' => 0,
            'deduction_profile_id' => DeductionProfile::factory(),
        ];
    }
}
