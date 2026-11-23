<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

final class AuditService
{
    /**
     * @param array<string, mixed> $changes
     */
    public function log(?User $actor, string $action, ?string $subjectType = null, ?int $subjectId = null, array $changes = [], ?Request $request = null): AuditLog
    {
        $request = $request ?? request();

        return AuditLog::create([
            'actor_id' => $actor?->id,
            'actor_role' => $actor?->getRoleNames()->first() ?? 'guest',
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'ip' => $request->ip() ?? '127.0.0.1',
            'user_agent' => (string) ($request->userAgent() ?? ''),
            'changes' => $changes,
        ]);
    }
}
