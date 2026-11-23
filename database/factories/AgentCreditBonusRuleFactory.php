<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AgentCreditBonusRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgentCreditBonusRule>
 */
class AgentCreditBonusRuleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tier' => 1,
            'rate' => '0.050000',
            'active' => true,
            'created_by' => null,
        ];
    }
}
