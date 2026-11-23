<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\User;
use App\Services\AuditService;
use App\Services\LoginHistoryService;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly LoginHistoryService $history,
    ) {
    }

    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        $this->history->recordLogin($user);
        $this->audit->log($user, 'auth.login', User::class, $user->id, ['guard' => $event->guard]);
    }
}
