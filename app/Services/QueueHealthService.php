<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Live queue health from the database queue tables (QUE-03, REP-10).
 * Admin_Observability_Spec.md:34 — green: no failures, oldest < 5m;
 * amber: oldest 5–30m; red: any failure or oldest > 30m.
 */
final class QueueHealthService
{
    /**
     * @return array{pending: int, oldestMinutes: int, failed: int, state: string, checkedAt: string}
     */
    public function summary(): array
    {
        $pending = (int) DB::table('jobs')->count();
        $failed = (int) DB::table('failed_jobs')->count();
        $oldestCreated = DB::table('jobs')->min('created_at');
        $oldestMinutes = 0;

        if ($oldestCreated !== null) {
            $oldestMinutes = max(0, (int) floor((time() - (int) $oldestCreated) / 60));
        }

        $state = match (true) {
            $failed > 0 || $oldestMinutes > 30 => 'red',
            $oldestMinutes > 5 => 'amber',
            default => 'green',
        };

        return [
            'pending' => $pending,
            'oldestMinutes' => $oldestMinutes,
            'failed' => $failed,
            'state' => $state,
            'checkedAt' => Carbon::now()->setTimezone('Asia/Dhaka')->toIso8601String(),
        ];
    }
}
