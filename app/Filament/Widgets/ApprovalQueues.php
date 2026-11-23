<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\MoneyFlowService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ApprovalQueues extends BaseWidget
{
    protected ?string $heading = 'Approval Queues';

    protected ?string $description = 'Pending money requests · Credit Manager';

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.view') ?? false;
    }

    /**
     * @return array{deposits: array{count: int, oldestHours: int, state: string}, withdrawals: array{count: int, oldestHours: int, state: string}, releases: array{count: int, oldestHours: int, state: string}, state: string}
     */
    public function queues(): array
    {
        return app(MoneyFlowService::class)->queues();
    }

    public function state(): string
    {
        return $this->queues()['state'];
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $queues = $this->queues();
        $color = match ($queues['state']) {
            'green' => 'success',
            'amber' => 'warning',
            default => 'danger',
        };

        return [
            Stat::make('Deposits pending', (string) $queues['deposits']['count'])
                ->description('Oldest '.$queues['deposits']['oldestHours'].'h · Credit Manager')
                ->url('/admin/manual-deposits')
                ->color(match ($queues['deposits']['state']) {
                    'green' => 'success',
                    'amber' => 'warning',
                    default => 'danger',
                })
                ->icon('heroicon-o-wallet'),
            Stat::make('Withdrawals pending', (string) $queues['withdrawals']['count'])
                ->description('Oldest '.$queues['withdrawals']['oldestHours'].'h · Finance Manager')
                ->url('/admin/withdrawals')
                ->icon('heroicon-o-scale'),
            Stat::make('Capital releases pending', (string) $queues['releases']['count'])
                ->description('Oldest '.$queues['releases']['oldestHours'].'h · Finance Manager')
                ->url('/admin/capital-releases')
                ->icon('heroicon-o-clock'),
            Stat::make('Health', strtoupper($queues['state']))
                ->description('Live queue aging · Asia/Dhaka')
                ->color($color)
                ->icon('heroicon-o-check-circle'),
        ];
    }
}
