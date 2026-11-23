<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use App\Models\UserProfile;
use App\Services\AuditService;

final class UserProfileObserver
{
    public function __construct(private readonly AuditService $audit)
    {
    }

    public function created(UserProfile $profile): void
    {
        $this->record('user_profile.created', $profile);
    }

    public function updated(UserProfile $profile): void
    {
        $this->record('user_profile.updated', $profile, $profile->getChanges());
    }

    public function deleted(UserProfile $profile): void
    {
        $this->record('user_profile.deleted', $profile);
    }

    /**
     * @param array<string, mixed> $changes
     */
    private function record(string $action, UserProfile $profile, array $changes = []): void
    {
        $actor = auth()->user();

        if (! $actor instanceof User) {
            return;
        }

        $this->audit->log($actor, $action, UserProfile::class, $profile->id, $changes);
    }
}
