<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\PnlStatusService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PnlStatus extends BaseWidget
{
    protected ?string $heading = 'P&L Distribution';

    protected ?string $description = 'Daily symbol distributions · Owner: P&L Operator';

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.view') ?? false;
    }

    /**
     * @return array{hasData: bool, latestTradeDate: ?string, todayDistributed: int, activeSymbols: int, unresolvedReversals: int, state: string, message: ?string, checkedAt: string}
     */
    public function summary(): array
    {
        return app(PnlStatusService::class)->summary();
    }

    public function state(): string
    {
        return $this->summary()['state'];
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $summary = $this->summary();
        $color = match ($summary['state']) {
            'green' => 'success',
            'amber' => 'warning',
            default => 'danger',
        };

        return [
            Stat::make('Latest trade date', $summary['latestTradeDate'] ?? '—')
                ->description($summary['message'] ?? 'From pnl_distributions.trade_date')
                ->url('/admin/reports?tab=investment_pnl')
                ->color($color),
            Stat::make('Distributed today', (string) $summary['todayDistributed'])
                ->description('Distributions with status distributed')
                ->icon('heroicon-o-scale'),
            Stat::make('Active symbols', (string) $summary['activeSymbols'])
                ->description('Admin-defined symbol list')
                ->icon('heroicon-o-banknotes'),
            Stat::make('Health', strtoupper($summary['state']))
                ->description('Asia/Dhaka · '.$summary['checkedAt'].' · P&L Operator')
                ->color($color)
                ->icon('heroicon-o-check-circle'),
        ];
    }
}
