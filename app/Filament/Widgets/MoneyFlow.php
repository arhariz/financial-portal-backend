<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\MoneyFlowService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MoneyFlow extends BaseWidget
{
    protected ?string $heading = 'Money Flow';

    protected ?string $description = 'Live ledger aggregates · Owner: CFO / Admin';

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.view') ?? false;
    }

    /**
     * @return array{aum: string, deposits: string, withdrawals: string, netFlow: string, negativeDays: int, breakCount: int, state: string, checkedAt: string}
     */
    public function summary(): array
    {
        return app(MoneyFlowService::class)->summary();
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
            Stat::make('AUM', $summary['aum'])
                ->description('Sum of all wallet balances (USD)')
                ->icon('heroicon-o-banknotes'),
            Stat::make('Net flow (30d)', $summary['netFlow'])
                ->description('Deposits − withdrawals')
                ->color($color)
                ->icon('heroicon-o-arrow-trending-up'),
            Stat::make('Deposits (30d)', $summary['deposits'])
                ->description('Posted deposit credits')
                ->icon('heroicon-o-arrow-down-circle'),
            Stat::make('Withdrawals (30d)', $summary['withdrawals'])
                ->description('Posted withdrawal debits')
                ->icon('heroicon-o-arrow-up-circle'),
            Stat::make('Health', strtoupper($summary['state']))
                ->description('Asia/Dhaka · '.$summary['checkedAt'].' · CFO / Admin')
                ->color($color)
                ->icon('heroicon-o-clock'),
        ];
    }
}
