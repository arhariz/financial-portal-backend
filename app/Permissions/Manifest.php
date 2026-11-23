<?php

declare(strict_types=1);

namespace App\Permissions;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class Manifest
{
    public const ALL = '*';

    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return array_values(config('role_matrix.permissions', []));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function roles(): array
    {
        return config('role_matrix.roles', []);
    }

    /**
     * @return array{permissions_created: int, roles_created: int, assignments_updated: int}
     */
    public static function sync(): array
    {
        $guard = self::guard();
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        $permissionsCreated = 0;
        foreach (self::permissions() as $name) {
            if (Permission::findOrCreate($name, $guard)->wasRecentlyCreated) {
                $permissionsCreated++;
            }
        }

        $rolesCreated = 0;
        $assignmentsUpdated = 0;
        $all = self::permissions();

        foreach (self::roles() as $slug => $granted) {
            $role = Role::findOrCreate($slug, $guard);
            if ($role->wasRecentlyCreated) {
                $rolesCreated++;
            }

            $expected = self::expectedFor($granted, $all);
            $actual = $role->permissions()->pluck('name')->all();

            if ($expected === $actual) {
                continue;
            }

            $role->syncPermissions($expected);
            $assignmentsUpdated++;
        }

        $registrar->forgetCachedPermissions();

        return [
            'permissions_created' => $permissionsCreated,
            'roles_created' => $rolesCreated,
            'assignments_updated' => $assignmentsUpdated,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function check(): array
    {
        $guard = self::guard();
        $issues = [];

        $expectedPermissions = self::permissions();
        $dbPermissions = Permission::where('guard_name', $guard)->pluck('name')->all();

        foreach (array_diff($expectedPermissions, $dbPermissions) as $missing) {
            $issues[] = "missing permission: {$missing}";
        }
        foreach (array_diff($dbPermissions, $expectedPermissions) as $extra) {
            $issues[] = "orphan permission: {$extra}";
        }

        $expectedRoles = array_keys(self::roles());
        $dbRoles = Role::where('guard_name', $guard)->pluck('name')->all();

        foreach (array_diff($expectedRoles, $dbRoles) as $missing) {
            $issues[] = "missing role: {$missing}";
        }

        // Runtime-created roles (not declared in config) are admin data under Q-005 and are
        // intentionally not flagged here; sync() never deletes them either.

        foreach (self::roles() as $slug => $granted) {
            $role = Role::where('name', $slug)->where('guard_name', $guard)->first();
            if (! $role instanceof Role) {
                continue;
            }

            $expected = self::expectedFor($granted, $expectedPermissions);
            $actual = $role->permissions()->pluck('name')->all();

            foreach (array_diff($expected, $actual) as $missing) {
                $issues[] = "role {$slug} missing permission: {$missing}";
            }
            foreach (array_diff($actual, $expected) as $extra) {
                $issues[] = "role {$slug} extra permission: {$extra}";
            }
        }

        return $issues;
    }

    /**
     * @param  array<int, string>  $granted
     * @param  array<int, string>  $all
     * @return array<int, string>
     */
    private static function expectedFor(array $granted, array $all): array
    {
        $expected = $granted === [self::ALL] ? $all : array_values($granted);

        sort($expected);

        return $expected;
    }

    private static function guard(): string
    {
        return config('auth.defaults.guard', 'web');
    }
}
