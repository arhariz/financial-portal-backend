<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\NotificationChannel;
use App\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationTemplate>
 */
class NotificationTemplateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'test_'.fake()->unique()->lexify('?????'),
            'channel' => NotificationChannel::Email,
            'subject' => fake()->sentence(3),
            'body' => fake()->sentence(),
            'variables' => [],
            'enabled' => true,
        ];
    }
}
