<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_health_endpoint_returns_ok_json(): void
    {
        $response = $this->getJson('/health');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'app',
                'environment',
                'php',
                'laravel',
                'timestamp',
            ])
            ->assertJson([
                'status' => 'ok',
                'app' => config('app.name'),
                'environment' => 'testing',
            ]);
    }
}
