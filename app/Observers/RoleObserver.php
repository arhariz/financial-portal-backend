<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use App\Services\AuditService;
use Spatie\Permission\Models\Role;

final class RoleObserver
{
    public function __construct(private readonly AuditService $audit)
    {
    }

    public function created(Role $role): void
    {
        $this->record('role.created', $role);
    }

    public function updated(Role $role): void
    {
        $this->record('role.updated', $role);
    }

    public function deleted(Role $role): void
    {
        $this->record('role.deleted', $role);
    }

    private function record(string $action, Role $role): void
    {
        $actor = auth()->user();

        if (! $actor instanceof User) {
            return;
        }

        $this->audit->log($actor, $action, Role::class, $role->id, ['name' => $role->name]);
    }
}
