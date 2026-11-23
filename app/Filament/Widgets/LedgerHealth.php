<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\LedgerReconciliationService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LedgerHealth extends BaseWidget
{
    protected ?string $heading = 'Ledger Health';

    protected ?string $description = 'Live wallet reconciliation · Owner: Finance Manager';

    public static function canView(): bool
    {
        return auth()->user()?->can('reports.view') ?? false;
    }

    /**
     * @return array{
     *     breakCount: int,
     *     wallets: list<array{id: int, userId: int, type: string, balance: string, expected: string, difference: string, inBreak: bool}>,
     *     checkedAt: string
     * }
     */
    public function result(): array
    {
        return app(LedgerReconciliationService::class)->reconcile();
    }

    /**
     * Admin_Observability_Spec.md:33 — green when all wallets reconcile; amber for a
     * break under 1% of balance; red for any unreconciled money wallet (cash/profit/commission,
     * WAL-01) or a break of at least 1%.
     */
    public function state(): string
    {
        $result = $this->result();

        if ($result['breakCount'] === 0) {
            return 'green';
        }

        foreach ($result['wallets'] as $wallet) {
            if (! $wallet['inBreak']) {
                continue;
            }

            if (in_array($wallet['type'], ['cash', 'profit', 'commission'], true)) {
                return 'red';
            }

            $balance = LedgerReconciliationService::toMinorUnits($wallet['balance']);
            $difference = LedgerReconciliationService::toMinorUnits($wallet['difference']);

            if ($balance === 0 || (abs($difference) * 100) >= abs($balance)) {
                return 'red';
            }
        }

        return 'amber';
    }

    public function owner(): string
    {
        return 'Finance Manager';
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $result = $this->result();
        $state = $this->state();
        $color = match ($state) {
            'green' => 'success',
            'amber' => 'warning',
            default => 'danger',
        };
        $icon = match ($state) {
            'green' => 'heroicon-o-check-circle',
            'amber' => 'heroicon-o-exclamation-triangle',
            default => 'heroicon-o-x-circle',
        };

        return [
            Stat::make('Health', strtoupper($state))
                ->description('Ledger reconciliation state')
                ->color($color)
                ->icon($icon),
            Stat::make('Wallets checked', (string) count($result['wallets']))
                ->description('Balance vs credits − debits')
                ->icon('heroicon-o-wallet'),
            Stat::make('Breaks', (string) $result['breakCount'])
                ->description($result['breakCount'] === 0 ? 'All wallets reconcile' : 'Review wallet rows below')
                ->color($result['breakCount'] === 0 ? 'success' : 'danger')
                ->icon('heroicon-o-scale'),
            Stat::make('Checked at', $result['checkedAt'])
                ->description('Asia/Dhaka · '.$this->owner())
                ->icon('heroicon-o-clock'),
        ];
    }
}
