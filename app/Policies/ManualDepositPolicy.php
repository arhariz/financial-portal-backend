<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ManualDeposit;
use App\Models\User;

class ManualDepositPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('deposits.view');
    }

    public function view(User $user, ManualDeposit $record): bool
    {
        return $user->can('deposits.view');
    }

    public function approve(User $user, ManualDeposit $record): bool
    {
        return $user->can('deposits.approve');
    }

    public function reject(User $user, ManualDeposit $record): bool
    {
        return $user->can('deposits.approve');
    }

    // Deposits are created only by the user-facing API and never edited or
    // deleted by staff (FIL-05); corrections are reversal entries.
    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ManualDeposit $record): bool
    {
        return false;
    }

    public function delete(User $user, ManualDeposit $record): bool
    {
        return false;
    }
}
