<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DashboardLayout;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<DashboardLayout>
 */
class DashboardLayoutFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_id' => Role::create(['name' => 'layout-'.fake()->unique()->lexify('?????')])->id,
            'user_id' => null,
            'widgets' => [],
            'updated_by' => null,
        ];
    }
}
