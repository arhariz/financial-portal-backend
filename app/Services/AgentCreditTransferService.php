<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserStatus;
use App\Enums\WalletTransactionType;
use App\Enums\WalletType;
use App\Models\Agent;
use App\Models\AgentCreditTransfer;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Agent sells USD credit to an investor in-app (D-008): one atomic cash
 * wallet transfer with two immutable ledger rows and an audit entry.
 * No MLM commission or role bonus is created here (COM-05, D-096).
 */
final class AgentCreditTransferService
{
    public function __construct(
        private readonly WalletService $wallets,
        private readonly AuditService $audit,
        private readonly GuardService $guard,
    ) {
    }

    /**
     * @param array{investor: User, amount_usd: string} $data
     *
     * @return array{transfer: AgentCreditTransfer, created: bool}
     */
    public function transfer(User $agent, array $data, ?string $idempotencyKey): array
    {
        $isAgent = Agent::query()
            ->where('user_id', $agent->id)
            ->where('status', UserStatus::Active)
            ->exists();

        if (! $isAgent) {
            throw new AuthorizationException('Only an active agent can transfer purchased credit.');
        }

        $this->guard->ensureCanTransfer($agent);

        $investor = $data['investor'];

        if ($investor->status !== UserStatus::Active) {
            throw new AuthorizationException('A suspended account cannot receive credit.');
        }

        if (bccomp($data['amount_usd'], '0.0000', 4) <= 0) {
            throw ValidationException::withMessages(['amount_usd' => ['The transfer amount must be greater than zero.']]);
        }

        if ($idempotencyKey !== null) {
            $existing = $this->findByIdempotencyKey($agent, $idempotencyKey);

            if ($existing !== null) {
                return ['transfer' => $existing, 'created' => false];
            }
        }

        /** @var array{transfer: AgentCreditTransfer, created: bool} $result */
        $result = DB::transaction(fn (): array => $this->postTransfer($agent, $investor, $data['amount_usd'], $idempotencyKey));

        return $result;
    }

    private function findByIdempotencyKey(User $agent, string $key): ?AgentCreditTransfer
    {
        return AgentCreditTransfer::query()
            ->where('agent_id', $agent->id)
            ->where('idempotency_key', $key)
            ->first();
    }

    /**
     * @return array{transfer: AgentCreditTransfer, created: bool}
     */
    private function postTransfer(User $agent, User $investor, string $amount, ?string $idempotencyKey): array
    {
        try {
            $transfer = AgentCreditTransfer::create([
                'agent_id' => $agent->id,
                'investor_id' => $investor->id,
                'amount_usd' => $amount,
                'idempotency_key' => $idempotencyKey,
            ]);
        } catch (UniqueConstraintViolationException) {
            /** @var AgentCreditTransfer $existing */
            $existing = $this->findByIdempotencyKey($agent, (string) $idempotencyKey);

            return ['transfer' => $existing, 'created' => false];
        }

        $this->wallets->transfer(
            $agent,
            WalletType::Cash,
            WalletType::Cash,
            $amount,
            WalletTransactionType::TransferOut,
            AgentCreditTransfer::class,
            $transfer->id,
            $agent,
            WalletTransactionType::TransferOut,
            WalletTransactionType::TransferIn,
            $investor,
        );

        $this->audit->log($agent, 'agent_credit.transferred', AgentCreditTransfer::class, $transfer->id, [
            'investor_id' => $investor->id,
            'amount_usd' => $amount,
        ]);

        return ['transfer' => $transfer, 'created' => true];
    }
}
