<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserProfile;

class UserProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view') || $user->can('kyc.view');
    }

    public function view(User $user, UserProfile $record): bool
    {
        return $user->can('users.view') || $user->can('kyc.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.manage');
    }

    public function update(User $user, UserProfile $record): bool
    {
        return $user->can('users.manage');
    }

    public function delete(User $user, UserProfile $record): bool
    {
        return $user->can('users.manage');
    }
}
