<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ManualDepositStatus;
use App\Enums\WalletTransactionType;
use App\Enums\WalletType;
use App\Models\ManualDeposit;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Manual BDT deposit request flow: submit → admin approve/reject → cash
 * wallet credit. Amounts stay decimal strings; no binary-float money math
 * (MON-01/02/04, D-006, LOCK-05).
 */
final class ManualDepositService
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRates,
        private readonly WalletService $wallets,
        private readonly AuditService $audit,
        private readonly GuardService $guard,
    ) {
    }

    /**
     * @param array{amount_bdt: string, payment_method: string, proof_path: string} $data
     *
     * @return array{deposit: ManualDeposit, created: bool}
     */
    public function submit(User $user, array $data, ?string $idempotencyKey): array
    {
        $this->guard->ensureCanDeposit($user);

        if ($idempotencyKey !== null) {
            $existing = $this->findByIdempotencyKey($user, $idempotencyKey);

            if ($existing !== null) {
                return ['deposit' => $existing, 'created' => false];
            }
        }

        $rate = $this->exchangeRates->current();

        if ($rate === null) {
            throw new RuntimeException('No active exchange rate is configured.');
        }

        $amountUsd = bcdiv($data['amount_bdt'], (string) $rate->bdt_per_usd, 4);
        $comparison = bccomp($amountUsd, '0.0000', 4);

        if ($comparison <= 0) {
            throw new InvalidArgumentException('The deposit amount is too small at the current rate.');
        }

        /** @var array{deposit: ManualDeposit, created: bool} $result */
        $result = DB::transaction(fn (): array => $this->createPendingDeposit($user, $data, $rate->id, $amountUsd, $idempotencyKey));

        return $result;
    }

    /**
     * Approve a pending request and credit USD to the cash wallet atomically
     * with one immutable ledger row (MON-02/04/05, SEC-07).
     */
    public function approve(User $reviewer, ManualDeposit $deposit): ManualDeposit
    {
        if (! $reviewer->can('deposits.approve')) {
            throw new AuthorizationException('You are not allowed to approve deposits.');
        }

        return DB::transaction(function () use ($reviewer, $deposit): ManualDeposit {
            $locked = ManualDeposit::query()->where('id', $deposit->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== ManualDepositStatus::Pending) {
                throw new RuntimeException('Only a pending deposit can be approved.');
            }

            $transaction = $this->wallets->deposit(
                $locked->user()->firstOrFail(),
                WalletType::Cash,
                (string) $locked->amount_usd,
                WalletTransactionType::Deposit,
                ManualDeposit::class,
                $locked->id,
                $reviewer,
            );

            $locked->status = ManualDepositStatus::Approved;
            $locked->reviewed_by = $reviewer->id;
            $locked->reviewed_at = now();
            $locked->wallet_transaction_id = $transaction->id;
            $locked->save();

            $this->audit->log($reviewer, 'deposit.approved', ManualDeposit::class, $locked->id, [
                'amount_usd' => (string) $locked->amount_usd,
                'wallet_transaction_id' => $transaction->id,
            ]);

            return $locked;
        });
    }

    /**
     * Reject a pending request with a mandatory reason — no wallet posting.
     */
    public function reject(User $reviewer, ManualDeposit $deposit, string $reason): ManualDeposit
    {
        if (! $reviewer->can('deposits.approve')) {
            throw new AuthorizationException('You are not allowed to review deposits.');
        }

        if (trim($reason) === '') {
            throw new InvalidArgumentException('A rejection reason is required.');
        }

        return DB::transaction(function () use ($reviewer, $deposit, $reason): ManualDeposit {
            $locked = ManualDeposit::query()->where('id', $deposit->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== ManualDepositStatus::Pending) {
                throw new RuntimeException('Only a pending deposit can be rejected.');
            }

            $locked->status = ManualDepositStatus::Rejected;
            $locked->reviewed_by = $reviewer->id;
            $locked->reviewed_at = now();
            $locked->rejection_reason = $reason;
            $locked->save();

            $this->audit->log($reviewer, 'deposit.rejected', ManualDeposit::class, $locked->id, [
                'rejection_reason' => $reason,
            ]);

            return $locked;
        });
    }

    private function findByIdempotencyKey(User $user, string $key): ?ManualDeposit
    {
        return ManualDeposit::query()
            ->where('user_id', $user->id)
            ->where('idempotency_key', $key)
            ->first();
    }

    /**
     * @param array{amount_bdt: string, payment_method: string, proof_path: string} $data
     *
     * @return array{deposit: ManualDeposit, created: bool}
     */
    private function createPendingDeposit(User $user, array $data, int $rateId, string $amountUsd, ?string $idempotencyKey): array
    {
        try {
            $deposit = ManualDeposit::create([
                'user_id' => $user->id,
                'amount_bdt' => $data['amount_bdt'],
                'amount_usd' => $amountUsd,
                'exchange_rate_id' => $rateId,
                'payment_method' => $data['payment_method'],
                'proof_path' => $data['proof_path'],
                'idempotency_key' => $idempotencyKey,
                'status' => ManualDepositStatus::Pending,
            ]);
        } catch (UniqueConstraintViolationException) {
            // A concurrent request with the same key won the race (API-06).
            /** @var ManualDeposit $existing */
            $existing = $this->findByIdempotencyKey($user, (string) $idempotencyKey);

            return ['deposit' => $existing, 'created' => false];
        }

        $this->audit->log($user, 'deposit.submitted', ManualDeposit::class, $deposit->id, [
            'amount_bdt' => $data['amount_bdt'],
            'amount_usd' => $amountUsd,
        ]);

        return ['deposit' => $deposit, 'created' => true];
    }
}
