<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\WalletTransactionType;
use App\Enums\WalletType;
use App\Enums\WithdrawalSourceWalletType;
use App\Enums\WithdrawalStatus;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Withdrawal request flow: request → approve → paid (D-007, LOCK-06).
 * Profit is withdrawable anytime; the payment step is the single atomic
 * wallet debit, with KYC and suspension enforced in this layer (SEC-02/03).
 */
final class WithdrawalService
{
    public function __construct(
        private readonly WalletService $wallets,
        private readonly AuditService $audit,
        private readonly GuardService $guard,
    ) {
    }

    /**
     * @param array{amount_usd: string, source_wallet_type: WithdrawalSourceWalletType, payout_method: string, payout_details: array<string, mixed>} $data
     *
     * @return array{withdrawal: WithdrawalRequest, created: bool}
     */
    public function request(User $user, array $data, ?string $idempotencyKey): array
    {
        $this->guard->ensureCanWithdraw($user);

        if ($idempotencyKey !== null) {
            $existing = $this->findByIdempotencyKey($user, $idempotencyKey);

            if ($existing !== null) {
                return ['withdrawal' => $existing, 'created' => false];
            }
        }

        /** @var array{withdrawal: WithdrawalRequest, created: bool} $result */
        $result = DB::transaction(fn (): array => $this->createPendingRequest($user, $data, $idempotencyKey));

        return $result;
    }

    /**
     * Staff approval only records intent; no money moves until payment.
     */
    public function approve(User $reviewer, WithdrawalRequest $withdrawal): WithdrawalRequest
    {
        if (! $reviewer->can('withdrawals.approve')) {
            throw new AuthorizationException('You are not allowed to approve withdrawals.');
        }

        return DB::transaction(function () use ($reviewer, $withdrawal): WithdrawalRequest {
            $locked = WithdrawalRequest::query()->where('id', $withdrawal->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== WithdrawalStatus::Pending) {
                throw new RuntimeException('Only a pending withdrawal can be approved.');
            }

            $locked->status = WithdrawalStatus::Approved;
            $locked->approved_by = $reviewer->id;
            $locked->approved_at = now();
            $locked->save();

            $this->audit->log($reviewer, 'withdrawal.approved', WithdrawalRequest::class, $locked->id, [
                'amount_usd' => (string) $locked->amount_usd,
            ]);

            return $locked;
        });
    }

    public function reject(User $reviewer, WithdrawalRequest $withdrawal, string $reason): WithdrawalRequest
    {
        if (! $reviewer->can('withdrawals.approve')) {
            throw new AuthorizationException('You are not allowed to review withdrawals.');
        }

        if (trim($reason) === '') {
            throw new InvalidArgumentException('A rejection reason is required.');
        }

        return DB::transaction(function () use ($reviewer, $withdrawal, $reason): WithdrawalRequest {
            $locked = WithdrawalRequest::query()->where('id', $withdrawal->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== WithdrawalStatus::Pending) {
                throw new RuntimeException('Only a pending withdrawal can be rejected.');
            }

            $locked->status = WithdrawalStatus::Rejected;
            $locked->approved_by = $reviewer->id;
            $locked->approved_at = now();
            $locked->rejection_reason = $reason;
            $locked->save();

            $this->audit->log($reviewer, 'withdrawal.rejected', WithdrawalRequest::class, $locked->id, [
                'rejection_reason' => $reason,
            ]);

            return $locked;
        });
    }

    /**
     * The single money-moving step: one atomic debit + immutable ledger row,
     * guarded against double payment and insufficient funds.
     */
    public function markPaid(User $reviewer, WithdrawalRequest $withdrawal, string $note): WithdrawalRequest
    {
        if (! $reviewer->can('withdrawals.approve')) {
            throw new AuthorizationException('You are not allowed to pay withdrawals.');
        }

        if (trim($note) === '') {
            throw new InvalidArgumentException('A payment reference is required.');
        }

        return DB::transaction(function () use ($reviewer, $withdrawal, $note): WithdrawalRequest {
            $locked = WithdrawalRequest::query()->where('id', $withdrawal->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== WithdrawalStatus::Approved) {
                throw new RuntimeException('Only an approved withdrawal can be paid.');
            }

            $walletType = match ($locked->source_wallet_type) {
                WithdrawalSourceWalletType::Cash => WalletType::Cash,
                WithdrawalSourceWalletType::Profit => WalletType::Profit,
            };

            $transaction = $this->wallets->withdraw(
                $locked->user()->firstOrFail(),
                $walletType,
                (string) $locked->amount_usd,
                WalletTransactionType::Withdrawal,
                WithdrawalRequest::class,
                $locked->id,
                $reviewer,
            );

            $locked->status = WithdrawalStatus::Paid;
            $locked->paid_at = now();
            $locked->wallet_transaction_id = $transaction->id;
            $locked->save();

            $this->audit->log($reviewer, 'withdrawal.paid', WithdrawalRequest::class, $locked->id, [
                'amount_usd' => (string) $locked->amount_usd,
                'payment_reference' => $note,
                'wallet_transaction_id' => $transaction->id,
            ]);

            return $locked;
        });
    }

    private function findByIdempotencyKey(User $user, string $key): ?WithdrawalRequest
    {
        return WithdrawalRequest::query()
            ->where('user_id', $user->id)
            ->where('idempotency_key', $key)
            ->first();
    }

    /**
     * @param array{amount_usd: string, source_wallet_type: WithdrawalSourceWalletType, payout_method: string, payout_details: array<string, mixed>} $data
     *
     * @return array{withdrawal: WithdrawalRequest, created: bool}
     */
    private function createPendingRequest(User $user, array $data, ?string $idempotencyKey): array
    {
        try {
            $withdrawal = WithdrawalRequest::create([
                'user_id' => $user->id,
                'source_wallet_type' => $data['source_wallet_type'],
                'amount_usd' => $data['amount_usd'],
                'payout_method' => $data['payout_method'],
                'payout_details' => $data['payout_details'],
                'idempotency_key' => $idempotencyKey,
                'status' => WithdrawalStatus::Pending,
            ]);
        } catch (UniqueConstraintViolationException) {
            /** @var WithdrawalRequest $existing */
            $existing = $this->findByIdempotencyKey($user, (string) $idempotencyKey);

            return ['withdrawal' => $existing, 'created' => false];
        }

        $this->audit->log($user, 'withdrawal.requested', WithdrawalRequest::class, $withdrawal->id, [
            'amount_usd' => $data['amount_usd'],
            'source_wallet_type' => $data['source_wallet_type']->value,
        ]);

        return ['withdrawal' => $withdrawal, 'created' => true];
    }
}
