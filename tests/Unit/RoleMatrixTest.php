<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Permissions\Manifest;
use Tests\TestCase;

class RoleMatrixTest extends TestCase
{
    public function test_every_granted_permission_is_declared(): void
    {
        $declared = Manifest::permissions();

        foreach (Manifest::roles() as $role => $granted) {
            if ($granted === [Manifest::ALL]) {
                continue;
            }

            foreach ($granted as $permission) {
                $this->assertContains($permission, $declared, "Role {$role} references undeclared permission: {$permission}");
            }
        }
    }

    public function test_super_admin_and_admin_receive_every_permission(): void
    {
        $roles = Manifest::roles();

        $this->assertSame([Manifest::ALL], $roles['super_admin'] ?? []);
        $this->assertSame([Manifest::ALL], $roles['admin'] ?? []);
    }

    public function test_declared_permissions_contain_no_duplicates(): void
    {
        $permissions = Manifest::permissions();

        $this->assertSame(array_values(array_unique($permissions)), $permissions);
    }

    public function test_panel_access_permission_is_declared(): void
    {
        $this->assertContains('access-admin-panel', Manifest::permissions());
    }
}
