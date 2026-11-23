<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserStatus;
use App\Enums\WalletTransactionType;
use App\Enums\WalletType;
use App\Models\Agent;
use App\Models\AgentCreditBonusRule;
use App\Models\AgentCreditPurchase;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Agent credit purchase (D-008, D-096, COM-05): BDT at the current admin
 * rate → USD face value to cash + an admin-configured tier bonus to the
 * commission wallet. The bonus is never an MLM commission and no upline
 * row is created.
 */
final class AgentCreditService
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRates,
        private readonly WalletService $wallets,
        private readonly AuditService $audit,
        private readonly GuardService $guard,
    ) {
    }

    /**
     * @param array{agent: User, amount_bdt: string} $data
     *
     * @return array{purchase: AgentCreditPurchase, created: bool}
     */
    public function purchase(User $admin, array $data, ?string $idempotencyKey): array
    {
        if (! $admin->can('agent_credit.manage')) {
            throw new AuthorizationException('You are not allowed to record agent credit purchases.');
        }

        $agent = $data['agent'];
        $agentRecord = Agent::query()
            ->where('user_id', $agent->id)
            ->where('status', UserStatus::Active)
            ->first();

        if ($agentRecord === null) {
            throw ValidationException::withMessages(['agent_id' => ['The selected user is not an active agent.']]);
        }

        $this->guard->ensureCanTransfer($agent);

        if ($idempotencyKey !== null) {
            $existing = $this->findByIdempotencyKey($agent, $idempotencyKey);

            if ($existing !== null) {
                return ['purchase' => $existing, 'created' => false];
            }
        }

        $rate = $this->exchangeRates->current();

        if ($rate === null) {
            throw new RuntimeException('No active exchange rate is configured.');
        }

        $amountUsd = bcdiv($data['amount_bdt'], (string) $rate->bdt_per_usd, 4);
        $comparison = bccomp($amountUsd, '0.0000', 4);

        if ($comparison <= 0) {
            throw ValidationException::withMessages(['amount_bdt' => ['The credit amount is too small at the current rate.']]);
        }

        $bonusRule = AgentCreditBonusRule::query()
            ->where('tier', $agentRecord->tier)
            ->where('active', true)
            ->first();

        $bonus = $bonusRule === null ? '0.0000' : bcmul($amountUsd, (string) $bonusRule->rate, 4);

        /** @var array{purchase: AgentCreditPurchase, created: bool} $result */
        $result = DB::transaction(fn (): array => $this->postPurchase(
            $admin,
            $agent,
            $agentRecord->tier,
            $bonusRule?->id,
            $data['amount_bdt'],
            $amountUsd,
            $bonus,
            $rate->id,
            $idempotencyKey,
        ));

        return $result;
    }

    private function findByIdempotencyKey(User $agent, string $key): ?AgentCreditPurchase
    {
        return AgentCreditPurchase::query()
            ->where('user_id', $agent->id)
            ->where('idempotency_key', $key)
            ->first();
    }

    /**
     * @return array{purchase: AgentCreditPurchase, created: bool}
     */
    private function postPurchase(
        User $admin,
        User $agent,
        int $agentTier,
        ?int $bonusRuleId,
        string $amountBdt,
        string $amountUsd,
        string $bonus,
        int $rateId,
        ?string $idempotencyKey,
    ): array {
        try {
            $purchase = AgentCreditPurchase::create([
                'user_id' => $agent->id,
                'amount_bdt' => $amountBdt,
                'amount_usd' => $amountUsd,
                'bonus_amount_usd' => $bonus,
                'bonus_rule_id' => $bonusRuleId,
                'agent_tier' => $agentTier,
                'exchange_rate_id' => $rateId,
                'idempotency_key' => $idempotencyKey,
                'created_by' => $admin->id,
            ]);
        } catch (UniqueConstraintViolationException) {
            /** @var AgentCreditPurchase $existing */
            $existing = $this->findByIdempotencyKey($agent, (string) $idempotencyKey);

            return ['purchase' => $existing, 'created' => false];
        }

        $this->wallets->deposit(
            $agent,
            WalletType::Cash,
            $amountUsd,
            WalletTransactionType::Deposit,
            AgentCreditPurchase::class,
            $purchase->id,
            $admin,
        );

        if (bccomp($bonus, '0.0000', 4) > 0) {
            $this->wallets->deposit(
                $agent,
                WalletType::Commission,
                $bonus,
                WalletTransactionType::RoleBonus,
                AgentCreditPurchase::class,
                $purchase->id,
                $admin,
            );
        }

        $this->audit->log($admin, 'agent_credit.purchased', AgentCreditPurchase::class, $purchase->id, [
            'amount_bdt' => $amountBdt,
            'amount_usd' => $amountUsd,
            'bonus_amount_usd' => $bonus,
            'agent_tier' => $agentTier,
            'bonus_rule_id' => $bonusRuleId,
            'agent_id' => $agent->id,
        ]);

        return ['purchase' => $purchase, 'created' => true];
    }
}
