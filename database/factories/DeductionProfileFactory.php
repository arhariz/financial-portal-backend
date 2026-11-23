<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DeductionProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeductionProfile>
 */
class DeductionProfileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'test_'.fake()->unique()->lexify('?????'),
            'rules' => ['type' => 'test', 'enabled' => true],
            'created_by' => null,
        ];
    }
}
