<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Reports extends Page
{
    protected string $view = 'filament.pages.reports';

    protected static string | \UnitEnum | null $navigationGroup = 'Reports';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('reports.view') ?? false;
    }

    /**
     * The eight report tabs locked by docs/Admin_Observability_Spec.md (REP-09).
     *
     * @return array<string, string>
     */
    public function getTabs(): array
    {
        return [
            'overview' => 'Overview',
            'ledger_wallets' => 'Ledger & Wallets',
            'investment_pnl' => 'Investment & P&L',
            'agents_commissions' => 'Agents & Commissions',
            'kyc_security' => 'KYC & Security',
            'money_flow' => 'Money Flow',
            'operations' => 'Operations',
            'exports' => 'Exports',
        ];
    }
}
