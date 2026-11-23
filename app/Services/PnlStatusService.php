<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PnlDistributionStatus;
use App\Enums\SymbolStatus;
use App\Models\PnlDistribution;
use App\Models\Symbol;
use Illuminate\Support\Carbon;

/**
 * P&L distribution health (PNL-04, REP-08/10). WP5-T05 has not landed yet,
 * so an empty table is reported honestly instead of inventing a trade date.
 */
final class PnlStatusService
{
    /**
     * @return array{hasData: bool, latestTradeDate: ?string, todayDistributed: int, activeSymbols: int, unresolvedReversals: int, state: string, message: ?string, checkedAt: string}
     */
    public function summary(): array
    {
        $total = PnlDistribution::count();
        $activeSymbols = Symbol::query()->where('status', SymbolStatus::Active)->count();
        $latestTradeDate = PnlDistribution::query()->max('trade_date');
        $todayDistributed = PnlDistribution::query()
            ->where('trade_date', now()->toDateString())
            ->where('status', PnlDistributionStatus::Distributed)
            ->count();
        $unresolvedReversals = PnlDistribution::query()
            ->where('status', PnlDistributionStatus::Reversed)
            ->whereNull('reversal_of_id')
            ->count();

        if ($total === 0) {
            return [
                'hasData' => false,
                'latestTradeDate' => null,
                'todayDistributed' => 0,
                'activeSymbols' => $activeSymbols,
                'unresolvedReversals' => 0,
                'state' => 'amber',
                'message' => 'No distributions posted yet — requires WP5-T05.',
                'checkedAt' => Carbon::now()->setTimezone('Asia/Dhaka')->toIso8601String(),
            ];
        }

        $state = match (true) {
            $unresolvedReversals > 0 => 'red',
            $activeSymbols > 0 && $todayDistributed === 0 => 'amber',
            default => 'green',
        };

        return [
            'hasData' => true,
            'latestTradeDate' => is_string($latestTradeDate) ? $latestTradeDate : null,
            'todayDistributed' => $todayDistributed,
            'activeSymbols' => $activeSymbols,
            'unresolvedReversals' => $unresolvedReversals,
            'state' => $state,
            'message' => null,
            'checkedAt' => Carbon::now()->setTimezone('Asia/Dhaka')->toIso8601String(),
        ];
    }
}
