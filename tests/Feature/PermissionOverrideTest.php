<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PermissionOverrideTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_grant_an_extra_permission_to_a_user(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'support@forexbd.test')->firstOrFail();
        $permission = Permission::where('name', 'symbols.manage')->firstOrFail();

        $this->assertFalse($user->can('symbols.manage'));

        $user->givePermissionTo($permission);

        $this->assertTrue($user->fresh()->can('symbols.manage'));
    }

    public function test_override_is_additive_and_cannot_remove_a_role_granted_permission(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'support@forexbd.test')->firstOrFail();

        // support has reports.view via role
        $this->assertTrue($user->can('reports.view'));

        // granting an override does not remove the role grant
        $user->givePermissionTo('symbols.manage');
        $this->assertTrue($user->fresh()->can('reports.view'));
        $this->assertTrue($user->fresh()->can('symbols.manage'));

        // revoking the override removes only the extra grant, not the role grant
        $user->revokePermissionTo('symbols.manage');
        $this->assertFalse($user->fresh()->can('symbols.manage'));
        $this->assertTrue($user->fresh()->can('reports.view'));
    }

    public function test_override_does_not_subtract_a_role_granted_permission(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'support@forexbd.test')->firstOrFail();

        // Attempting to revoke a role-granted permission leaves it granted
        $user->revokePermissionTo('reports.view');

        $this->assertTrue($user->fresh()->can('reports.view'));
    }

    public function test_super_admin_can_open_the_user_edit_page(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@forexbd.test')->firstOrFail();
        $target = User::where('email', 'support@forexbd.test')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/users/'.$target->id.'/edit')
            ->assertOk()
            ->assertSee('permissions');
    }

    public function test_support_cannot_manage_permission_overrides(): void
    {
        $this->seed(DatabaseSeeder::class);

        $support = User::where('email', 'support@forexbd.test')->firstOrFail();
        $target = User::where('email', 'investor@forexbd.test')->firstOrFail();

        $this->assertFalse($support->can('users.manage'));
        $this->actingAs($support)
            ->get('/admin/users/'.$target->id.'/edit')
            ->assertForbidden();
    }

    public function test_investor_cannot_reach_the_users_resource(): void
    {
        $this->seed(DatabaseSeeder::class);

        $investor = User::where('email', 'investor@forexbd.test')->firstOrFail();

        $response = $this->actingAs($investor)->get('/admin/users');
        $this->assertFalse($response->isSuccessful());
    }
}
