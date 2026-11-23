<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\LedgerReconciliationService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LedgerWalletsTable extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->can('reports.view') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Wallet reconciliation')
            ->records(fn (): array => app(LedgerReconciliationService::class)->reconcile()['wallets'])
            ->columns([
                TextColumn::make('id')
                    ->label('Wallet')
                    ->url(fn (array $record): string => url('/admin/reports?tab=ledger_wallets&wallet='.$record['id'])),
                TextColumn::make('type')->badge(),
                TextColumn::make('balance')->label('Balance (USD)'),
                TextColumn::make('expected')->label('Expected (USD)'),
                TextColumn::make('difference')
                    ->label('Difference')
                    ->color(fn (array $record): string => $record['inBreak'] ? 'danger' : 'success'),
                TextColumn::make('inBreak')
                    ->label('Status')
                    ->getStateUsing(fn (array $record): string => $record['inBreak'] ? 'BREAK' : 'OK')
                    ->badge()
                    ->color(fn (array $record): string => $record['inBreak'] ? 'danger' : 'success'),
            ])
            ->emptyStateHeading('No ledger data');
    }
}
