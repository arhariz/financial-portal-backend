<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;

final class LoginHistoryService
{
    public function recordLogin(User $user, ?Request $request = null): LoginHistory
    {
        $request = $request ?? request();

        return LoginHistory::create([
            'user_id' => $user->id,
            'ip' => $request->ip() ?? '127.0.0.1',
            'user_agent' => (string) ($request->userAgent() ?? ''),
            'device' => null,
            'logged_in_at' => now(),
            'logged_out_at' => null,
        ]);
    }

    public function recordLogout(LoginHistory $entry): void
    {
        $entry->update(['logged_out_at' => now()]);
    }
}
