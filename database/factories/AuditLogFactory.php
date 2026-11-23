<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_id' => User::factory(),
            'actor_role' => 'investor',
            'action' => 'test.action',
            'subject_type' => null,
            'subject_id' => null,
            'ip' => '127.0.0.1',
            'user_agent' => 'test-agent',
            'changes' => [],
        ];
    }
}
