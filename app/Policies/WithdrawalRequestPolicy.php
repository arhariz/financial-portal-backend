<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WithdrawalRequest;

class WithdrawalRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('withdrawals.view');
    }

    public function view(User $user, WithdrawalRequest $record): bool
    {
        return $user->can('withdrawals.view');
    }

    public function approve(User $user, WithdrawalRequest $record): bool
    {
        return $user->can('withdrawals.approve');
    }

    public function reject(User $user, WithdrawalRequest $record): bool
    {
        return $user->can('withdrawals.approve');
    }

    public function pay(User $user, WithdrawalRequest $record): bool
    {
        return $user->can('withdrawals.approve');
    }

    // Requests originate only from the user-facing API; staff never edits or
    // deletes them (FIL-05) — corrections are reversal entries.
    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, WithdrawalRequest $record): bool
    {
        return false;
    }

    public function delete(User $user, WithdrawalRequest $record): bool
    {
        return false;
    }
}
