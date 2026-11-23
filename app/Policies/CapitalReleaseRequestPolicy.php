<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CapitalReleaseRequest;
use App\Models\User;

class CapitalReleaseRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('capital_releases.view');
    }

    public function view(User $user, CapitalReleaseRequest $record): bool
    {
        return $user->can('capital_releases.view');
    }

    public function approve(User $user, CapitalReleaseRequest $record): bool
    {
        return $user->can('capital_releases.approve');
    }

    public function reject(User $user, CapitalReleaseRequest $record): bool
    {
        return $user->can('capital_releases.approve');
    }

    // Requests originate from the API; the 24h release is performed by the
    // queued job, never by an editable staff form (FIL-05).
    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, CapitalReleaseRequest $record): bool
    {
        return false;
    }

    public function delete(User $user, CapitalReleaseRequest $record): bool
    {
        return false;
    }
}
