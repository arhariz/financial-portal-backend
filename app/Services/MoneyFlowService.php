<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ManualDepositStatus;
use App\Enums\TransactionDirection;
use App\Enums\WalletTransactionType;
use App\Enums\WithdrawalStatus;
use App\Enums\CapitalReleaseStatus;
use App\Models\CapitalReleaseRequest;
use App\Models\ManualDeposit;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Carbon;

/**
 * Live money-flow aggregates for the WP9-T04 widgets. All amounts stay
 * integer-scaled at 4dp — no binary-float money math (MON-01, TST-05).
 */
final class MoneyFlowService
{
    public function __construct(private readonly LedgerReconciliationService $reconciliation)
    {
    }

    /**
     * @return array{aum: string, deposits: string, withdrawals: string, netFlow: string, negativeDays: int, breakCount: int, state: string, checkedAt: string}
     */
    public function summary(): array
    {
        $aumUnits = 0;
        foreach (Wallet::query()->get(['balance']) as $wallet) {
            $aumUnits += LedgerReconciliationService::toMinorUnits((string) $wallet->balance);
        }

        $since = now()->subDays(30);
        $depositUnits = $this->flowUnits($since, WalletTransactionType::Deposit, TransactionDirection::Credit);
        $withdrawalUnits = $this->flowUnits($since, WalletTransactionType::Withdrawal, TransactionDirection::Debit);
        $netUnits = $depositUnits - $withdrawalUnits;

        $negativeDays = 0;
        for ($day = 0; $day < 3; $day++) {
            $start = now()->copy()->startOfDay()->subDays($day);
            $end = $start->copy()->endOfDay();
            $in = $this->flowUnitsBetween($start, $end, WalletTransactionType::Deposit, TransactionDirection::Credit);
            $out = $this->flowUnitsBetween($start, $end, WalletTransactionType::Withdrawal, TransactionDirection::Debit);

            if ($in - $out < 0) {
                $negativeDays++;
            }
        }

        $breakCount = $this->reconciliation->reconcile()['breakCount'];
        $state = $breakCount > 0 ? 'red' : ($negativeDays >= 3 ? 'amber' : 'green');

        return [
            'aum' => LedgerReconciliationService::fromMinorUnits($aumUnits),
            'deposits' => LedgerReconciliationService::fromMinorUnits($depositUnits),
            'withdrawals' => LedgerReconciliationService::fromMinorUnits($withdrawalUnits),
            'netFlow' => LedgerReconciliationService::fromMinorUnits($netUnits),
            'negativeDays' => $negativeDays,
            'breakCount' => $breakCount,
            'state' => $state,
            'checkedAt' => now()->setTimezone('Asia/Dhaka')->toIso8601String(),
        ];
    }

    /**
     * @return array{deposits: array{count: int, oldestHours: int, state: string}, withdrawals: array{count: int, oldestHours: int, state: string}, releases: array{count: int, oldestHours: int, state: string}, state: string}
     */
    public function queues(): array
    {
        $deposits = $this->queue(
            ManualDeposit::query()->where('status', ManualDepositStatus::Pending)->pluck('created_at')->all(),
        );
        $withdrawals = $this->queue(
            WithdrawalRequest::query()->where('status', WithdrawalStatus::Pending)->pluck('requested_at')->all(),
        );
        $releases = $this->queue(
            CapitalReleaseRequest::query()->where('status', CapitalReleaseStatus::Pending)->pluck('requested_at')->all(),
        );

        $states = [$deposits['state'], $withdrawals['state'], $releases['state']];
        $state = in_array('red', $states, true) ? 'red' : (in_array('amber', $states, true) ? 'amber' : 'green');

        return [
            'deposits' => $deposits,
            'withdrawals' => $withdrawals,
            'releases' => $releases,
            'state' => $state,
        ];
    }

    /**
     * @param array<int, Carbon|null> $timestamps
     *
     * @return array{count: int, oldestHours: int, state: string}
     */
    private function queue(array $timestamps): array
    {
        $count = count($timestamps);
        $oldestHours = 0;

        foreach ($timestamps as $timestamp) {
            if ($timestamp !== null) {
                $oldestHours = max($oldestHours, (int) $timestamp->diffInHours(now()));
            }
        }

        $state = match (true) {
            $oldestHours > 48 => 'red',
            $oldestHours > 24 => 'amber',
            default => 'green',
        };

        return ['count' => $count, 'oldestHours' => $oldestHours, 'state' => $state];
    }

    private function flowUnits(Carbon $since, WalletTransactionType $type, TransactionDirection $direction): int
    {
        return $this->flowUnitsBetween($since, now(), $type, $direction);
    }

    private function flowUnitsBetween(Carbon $start, Carbon $end, WalletTransactionType $type, TransactionDirection $direction): int
    {
        $units = 0;
        $rows = WalletTransaction::query()
            ->where('type', $type)
            ->where('direction', $direction)
            ->whereBetween('created_at', [$start, $end])
            ->get(['amount']);

        foreach ($rows as $row) {
            $units += LedgerReconciliationService::toMinorUnits((string) $row->amount);
        }

        return $units;
    }
}
