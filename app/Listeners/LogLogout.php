<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\LoginHistory;
use App\Models\User;
use App\Services\AuditService;
use App\Services\LoginHistoryService;
use Illuminate\Auth\Events\Logout;

class LogLogout
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly LoginHistoryService $history,
    ) {
    }

    public function handle(Logout $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        $entry = LoginHistory::query()
            ->where('user_id', $user->id)
            ->whereNull('logged_out_at')
            ->latest('logged_in_at')
            ->first();

        if ($entry !== null) {
            $this->history->recordLogout($entry);
        }

        $this->audit->log($user, 'auth.logout', User::class, $user->id, []);
    }
}
