<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    public function __construct(private readonly AuditService $audit)
    {
    }

    public function handle(Failed $event): void
    {
        $user = $event->user instanceof User ? $event->user : null;

        $this->audit->log(
            $user,
            'auth.login_failed',
            null,
            null,
            ['email' => $event->credentials['email'] ?? null]
        );
    }
}
