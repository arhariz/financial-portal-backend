<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TransactionDirection;
use App\Models\Wallet;

class LedgerReconciliationService
{
    /**
     * Credits minus debits must equal the maintained wallet balance (MON-05 / Schema §3 #2).
     * All arithmetic is integer-scaled at 4dp — no binary floats are used.
     *
     * @return array{
     *     breakCount: int,
     *     wallets: list<array{id: int, userId: int, type: string, balance: string, expected: string, difference: string, inBreak: bool}>,
     *     checkedAt: string
     * }
     */
    public function reconcile(): array
    {
        $rows = [];
        $breakCount = 0;

        foreach (Wallet::query()->with('transactions')->orderBy('id')->get() as $wallet) {
            $balanceUnits = $this->toMinorUnits((string) $wallet->balance);
            $expectedUnits = 0;

            foreach ($wallet->transactions as $transaction) {
                $amountUnits = $this->toMinorUnits((string) $transaction->amount);
                $expectedUnits += $transaction->direction === TransactionDirection::Credit
                    ? $amountUnits
                    : -$amountUnits;
            }

            $differenceUnits = $balanceUnits - $expectedUnits;
            $inBreak = $differenceUnits !== 0;

            if ($inBreak) {
                $breakCount++;
            }

            $rows[] = [
                'id' => $wallet->id,
                'userId' => $wallet->user_id,
                'type' => $wallet->type->value,
                'balance' => $this->fromMinorUnits($balanceUnits),
                'expected' => $this->fromMinorUnits($expectedUnits),
                'difference' => $this->fromMinorUnits($differenceUnits),
                'inBreak' => $inBreak,
            ];
        }

        return [
            'breakCount' => $breakCount,
            'wallets' => $rows,
            'checkedAt' => now()->setTimezone('Asia/Dhaka')->toIso8601String(),
        ];
    }

    private function toMinorUnits(string $amount): int
    {
        $negative = str_starts_with($amount, '-');
        $digits = ltrim($amount, '+-');
        [$whole, $fraction] = array_pad(explode('.', $digits, 2), 2, '0');
        $fraction = substr($fraction.'0000', 0, 4);

        $units = ((int) $whole * 10000) + (int) $fraction;

        return $negative ? -$units : $units;
    }

    private function fromMinorUnits(int $units): string
    {
        $sign = $units < 0 ? '-' : '';
        $absolute = abs($units);

        return sprintf('%s%d.%04d', $sign, intdiv($absolute, 10000), $absolute % 10000);
    }
}
