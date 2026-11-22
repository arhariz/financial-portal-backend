<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Permissions\Manifest;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_expected_rbac(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('permissions', count(Manifest::permissions()));
        $this->assertDatabaseCount('roles', count(Manifest::roles()));
    }

    public function test_every_role_has_at_least_one_seeded_user(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'admin@forexbd.test']);

        foreach (Role::all() as $role) {
            $this->assertGreaterThanOrEqual(
                1,
                $role->users()->count(),
                "Role [{$role->name}] has no seeded user",
            );
        }
    }

    public function test_database_seeder_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('permissions', count(Manifest::permissions()));
        $this->assertDatabaseCount('roles', count(Manifest::roles()));

        foreach (Manifest::roles() as $slug => $granted) {
            $role = Role::where('name', $slug)->firstOrFail();

            $expected = $granted === [Manifest::ALL] ? Manifest::permissions() : $granted;
            $actual = $role->permissions()->pluck('name')->all();

            sort($expected);
            sort($actual);

            $this->assertSame($expected, $actual, "Role [{$slug}] permissions drifted after reseeding");
        }
    }
}
