<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\WalletTransaction;
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
     * Drill-down rows for the Ledger Health widget. Read-only; never mutates ledger data.
     *
     * @return list<array{id: int, direction: string, amount: string, type: string, balance_after: string}>
     */
    public function getDrilldownTransactions(): array
    {
        $walletId = request()->integer('wallet');

        if ($walletId === 0) {
            return [];
        }

        return WalletTransaction::query()
            ->where('wallet_id', $walletId)
            ->orderBy('id')
            ->get()
            ->map(fn (WalletTransaction $transaction): array => [
                'id' => $transaction->id,
                'direction' => $transaction->direction->value,
                'amount' => $transaction->amount,
                'type' => $transaction->type->value,
                'balance_after' => $transaction->balance_after,
            ])
            ->all();
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
