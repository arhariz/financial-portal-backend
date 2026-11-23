<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\CapitalReleaseRequest;
use App\Services\AuditService;
use App\Services\CapitalReleaseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Release approved capital after its 24h gate. Only the request ID crosses
 * the queue boundary (QUE-04); the service enforces idempotency (QUE-05).
 */
final class ReleaseCapital implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [60, 300, 900];

    public function __construct(public readonly int $releaseId)
    {
        $this->onQueue('capital-releases');
    }

    public function handle(CapitalReleaseService $service): void
    {
        $release = CapitalReleaseRequest::find($this->releaseId);

        if ($release === null) {
            return;
        }

        $service->release($release);
    }

    /**
     * A failed money job is never silently dropped (QUE-03).
     */
    public function failed(Throwable $exception): void
    {
        app(AuditService::class)->log(null, 'capital_release.failed', CapitalReleaseRequest::class, $this->releaseId, [
            'error' => $exception->getMessage(),
        ]);
    }
}
