<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Permissions\Manifest;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionMatrixTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_and_admin_hold_every_permission(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach (['admin@forexbd.test', 'admin2@forexbd.test'] as $email) {
            $user = User::where('email', $email)->firstOrFail();

            foreach (Manifest::permissions() as $permission) {
                $this->assertTrue($user->can($permission), "{$email} should have {$permission}");
            }
        }
    }

    public function test_each_seeded_role_matches_its_declared_permission_set(): void
    {
        $this->seed(DatabaseSeeder::class);

        $roles = Manifest::roles();

        foreach (Manifest::roles() as $role => $granted) {
            if ($granted === [Manifest::ALL]) {
                continue;
            }

            $user = User::role($role)->first();

            if ($user === null) {
                continue;
            }

            foreach (Manifest::permissions() as $permission) {
                $expected = in_array($permission, $granted, true);
                $this->assertSame(
                    $expected,
                    $user->can($permission),
                    "Role {$role}: {$permission} expected ".var_export($expected, true)
                );
            }
        }
    }

    public function test_support_is_view_only(): void
    {
        $this->seed(DatabaseSeeder::class);

        $support = User::where('email', 'support@forexbd.test')->firstOrFail();

        $this->assertTrue($support->can('access-admin-panel'));
        $this->assertTrue($support->can('dashboard.view'));
        $this->assertTrue($support->can('reports.view'));

        foreach (['users.manage', 'users.view', 'deposits.approve', 'withdrawals.approve', 'pnl.distribute', 'symbols.manage', 'commissions.configure'] as $permission) {
            $this->assertFalse($support->can($permission), "Support must not have {$permission}");
        }
    }

    public function test_investor_and_agent_tiers_have_no_permissions(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach (['investor@forexbd.test', 'agent1@forexbd.test', 'agent2@forexbd.test', 'agent3@forexbd.test'] as $email) {
            $user = User::where('email', $email)->firstOrFail();

            $this->assertFalse($user->can('access-admin-panel'), "{$email} must not access admin panel");
            $this->assertFalse($user->can('reports.view'), "{$email} must not view reports");
        }
    }

    public function test_unknown_ability_is_denied_for_every_role(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach (User::all() as $user) {
            $this->assertFalse($user->can('totally.made.up.ability'));
        }
    }

    public function test_all_twelve_roles_are_present_in_the_matrix(): void
    {
        $this->assertCount(12, Manifest::roles());
    }
}
