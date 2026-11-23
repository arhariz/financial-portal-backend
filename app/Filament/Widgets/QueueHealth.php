<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\QueueHealthService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QueueHealth extends BaseWidget
{
    protected ?string $heading = 'Queue Health';

    protected ?string $description = 'Database queue · Owner: P&L Operator';

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.view') ?? false;
    }

    /**
     * @return array{pending: int, oldestMinutes: int, failed: int, state: string, checkedAt: string}
     */
    public function summary(): array
    {
        return app(QueueHealthService::class)->summary();
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
            Stat::make('Pending jobs', (string) $summary['pending'])
                ->description('Database queue backlog')
                ->url('/admin/reports?tab=operations')
                ->icon('heroicon-o-clock'),
            Stat::make('Oldest queued', $summary['oldestMinutes'].' min')
                ->description('Green < 5m · Amber 5–30m · Red > 30m')
                ->color($color)
                ->icon('heroicon-o-clock'),
            Stat::make('Failed jobs', (string) $summary['failed'])
                ->description('Any failure is red')
                ->url('/admin/reports?tab=operations')
                ->color($summary['failed'] > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-x-circle'),
            Stat::make('Health', strtoupper($summary['state']))
                ->description('Asia/Dhaka · '.$summary['checkedAt'].' · P&L Operator')
                ->color($color)
                ->icon('heroicon-o-check-circle'),
        ];
    }
}
