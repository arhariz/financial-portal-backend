<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoginHistory>
 */
class LoginHistoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'ip' => '127.0.0.1',
            'user_agent' => 'test-agent',
            'device' => null,
            'logged_in_at' => now(),
            'logged_out_at' => null,
        ];
    }
}
