<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_roles_resource(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs(User::where('email', 'admin@forexbd.test')->firstOrFail())
            ->get('/admin/roles')
            ->assertOk();
    }

    public function test_support_cannot_view_roles_resource(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs(User::where('email', 'support@forexbd.test')->firstOrFail())
            ->get('/admin/roles')
            ->assertForbidden();
    }

    public function test_investor_cannot_reach_roles_resource(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->actingAs(User::where('email', 'investor@forexbd.test')->firstOrFail())
            ->get('/admin/roles');

        $this->assertFalse($response->isSuccessful());
    }

    public function test_runtime_role_does_not_cause_sync_drift(): void
    {
        $this->seed(DatabaseSeeder::class);

        $role = Role::create(['name' => 'runtime-test-role', 'guard_name' => 'web']);
        $role->syncPermissions([Permission::firstOrFail()->name]);

        $this->assertSame(0, Artisan::call('permissions:sync', ['--check' => true]));

        $this->assertDatabaseHas('roles', ['name' => 'runtime-test-role']);
    }

    public function test_role_policy_blocks_delete_when_users_are_assigned(): void
    {
        $this->seed(DatabaseSeeder::class);

        $role = Role::where('name', 'support')->firstOrFail();
        $admin = User::where('email', 'admin@forexbd.test')->firstOrFail();

        $this->assertFalse($admin->can('delete', $role));
    }

    public function test_role_creation_is_audited(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@forexbd.test')->firstOrFail();
        $this->actingAs($admin);

        Role::create(['name' => 'audited-role', 'guard_name' => 'web']);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'role.created',
        ]);
    }
}
