<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CapitalReleaseStatus;
use App\Enums\WalletTransactionType;
use App\Enums\WalletType;
use App\Jobs\ReleaseCapital;
use App\Models\CapitalReleaseRequest;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Capital release: request → admin approval → 24h queued release → cash
 * wallet, then a separate withdrawal request (D-007, TME-04, WAL-06).
 */
final class CapitalReleaseService
{
    public function __construct(
        private readonly WalletService $wallets,
        private readonly AuditService $audit,
        private readonly GuardService $guard,
    ) {
    }

    /**
     * @param array{amount_usd: string} $data
     *
     * @return array{release: CapitalReleaseRequest, created: bool}
     */
    public function request(User $user, array $data, ?string $idempotencyKey): array
    {
        $this->guard->ensureCanWithdraw($user);

        if ($idempotencyKey !== null) {
            $existing = $this->findByIdempotencyKey($user, $idempotencyKey);

            if ($existing !== null) {
                return ['release' => $existing, 'created' => false];
            }
        }

        /** @var array{release: CapitalReleaseRequest, created: bool} $result */
        $result = DB::transaction(fn (): array => $this->createPendingRequest($user, $data, $idempotencyKey));

        return $result;
    }

    /**
     * Approval records intent, starts the 24h clock and queues the release
     * job on a named queue (QUE-01, QUE-05).
     */
    public function approve(User $reviewer, CapitalReleaseRequest $release): CapitalReleaseRequest
    {
        if (! $reviewer->can('capital_releases.approve')) {
            throw new AuthorizationException('You are not allowed to approve capital releases.');
        }

        $approved = DB::transaction(function () use ($reviewer, $release): CapitalReleaseRequest {
            $locked = CapitalReleaseRequest::query()->where('id', $release->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== CapitalReleaseStatus::Pending) {
                throw new RuntimeException('Only a pending capital release can be approved.');
            }

            $releaseAt = now()->addDay();
            $locked->status = CapitalReleaseStatus::Approved;
            $locked->approved_by = $reviewer->id;
            $locked->approved_at = now();
            $locked->release_at = $releaseAt;
            $locked->save();

            $this->audit->log($reviewer, 'capital_release.approved', CapitalReleaseRequest::class, $locked->id, [
                'amount_usd' => (string) $locked->amount_usd,
                'release_at' => $releaseAt->toIso8601String(),
            ]);

            return $locked;
        });

        $releaseAt = $approved->release_at;

        if ($releaseAt === null) {
            throw new RuntimeException('The approved release has no 24h timestamp.');
        }

        ReleaseCapital::dispatch($approved->id)->delay($releaseAt);

        return $approved;
    }

    public function reject(User $reviewer, CapitalReleaseRequest $release, string $reason): CapitalReleaseRequest
    {
        if (! $reviewer->can('capital_releases.approve')) {
            throw new AuthorizationException('You are not allowed to review capital releases.');
        }

        if (trim($reason) === '') {
            throw new InvalidArgumentException('A rejection reason is required.');
        }

        return DB::transaction(function () use ($reviewer, $release, $reason): CapitalReleaseRequest {
            $locked = CapitalReleaseRequest::query()->where('id', $release->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== CapitalReleaseStatus::Pending) {
                throw new RuntimeException('Only a pending capital release can be rejected.');
            }

            $locked->status = CapitalReleaseStatus::Rejected;
            $locked->approved_by = $reviewer->id;
            $locked->approved_at = now();
            $locked->save();

            $this->audit->log($reviewer, 'capital_release.rejected', CapitalReleaseRequest::class, $locked->id, [
                'rejection_reason' => $reason,
            ]);

            return $locked;
        });
    }

    /**
     * The queued money move. Re-checks the 24h gate and current account
     * status; already-released requests are a no-op so retries cannot
     * double-credit (QUE-05, MON-04).
     */
    public function release(CapitalReleaseRequest $release): CapitalReleaseRequest
    {
        if ($release->status === CapitalReleaseStatus::Released) {
            return $release;
        }

        return DB::transaction(function () use ($release): CapitalReleaseRequest {
            $locked = CapitalReleaseRequest::query()->where('id', $release->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === CapitalReleaseStatus::Released) {
                return $locked;
            }

            if ($locked->status !== CapitalReleaseStatus::Approved) {
                throw new RuntimeException('Only an approved capital release can be released.');
            }

            $now = now();

            if ($now->lessThan($locked->release_at)) {
                throw new RuntimeException('The 24 hour release window has not elapsed.');
            }

            $user = $locked->user()->firstOrFail();
            $this->guard->ensureCanWithdraw($user);

            $credit = $this->wallets->transfer(
                $user,
                WalletType::Investment,
                WalletType::Cash,
                (string) $locked->amount_usd,
                WalletTransactionType::CapitalRelease,
                CapitalReleaseRequest::class,
                $locked->id,
            );

            $locked->status = CapitalReleaseStatus::Released;
            $locked->released_at = $now;
            $locked->wallet_transaction_id = $credit->id;
            $locked->save();

            $this->audit->log(null, 'capital_release.released', CapitalReleaseRequest::class, $locked->id, [
                'amount_usd' => (string) $locked->amount_usd,
                'wallet_transaction_id' => $credit->id,
            ]);

            return $locked;
        });
    }

    private function findByIdempotencyKey(User $user, string $key): ?CapitalReleaseRequest
    {
        return CapitalReleaseRequest::query()
            ->where('user_id', $user->id)
            ->where('idempotency_key', $key)
            ->first();
    }

    /**
     * @param array{amount_usd: string} $data
     *
     * @return array{release: CapitalReleaseRequest, created: bool}
     */
    private function createPendingRequest(User $user, array $data, ?string $idempotencyKey): array
    {
        try {
            $release = CapitalReleaseRequest::create([
                'user_id' => $user->id,
                'amount_usd' => $data['amount_usd'],
                'idempotency_key' => $idempotencyKey,
                'status' => CapitalReleaseStatus::Pending,
            ]);
        } catch (UniqueConstraintViolationException) {
            /** @var CapitalReleaseRequest $existing */
            $existing = $this->findByIdempotencyKey($user, (string) $idempotencyKey);

            return ['release' => $existing, 'created' => false];
        }

        $this->audit->log($user, 'capital_release.requested', CapitalReleaseRequest::class, $release->id, [
            'amount_usd' => $data['amount_usd'],
        ]);

        return ['release' => $release, 'created' => true];
    }
}
