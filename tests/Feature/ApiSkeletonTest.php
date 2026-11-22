<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiSkeletonTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_v1_health_returns_ok_json(): void
    {
        $response = $this->getJson('/api/v1/health');

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

    public function test_auth_sanctum_route_denies_without_token(): void
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401)
            ->assertJsonStructure(['message']);
    }

    public function test_auth_sanctum_route_responds_with_valid_token(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'admin@forexbd.test')->firstOrFail();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user');

        $response->assertOk()
            ->assertJsonPath('id', $user->id);
    }
}
