<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('roles.manage');
    }

    public function view(User $user, Role $record): bool
    {
        return $user->can('roles.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('roles.manage');
    }

    public function update(User $user, Role $record): bool
    {
        return $user->can('roles.manage');
    }

    public function delete(User $user, Role $record): bool
    {
        return $user->can('roles.manage') && $record->users()->count() === 0;
    }
}
