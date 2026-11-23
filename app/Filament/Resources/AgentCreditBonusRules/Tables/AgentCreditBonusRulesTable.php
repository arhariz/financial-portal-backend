<?php

declare(strict_types=1);

namespace App\Filament\Resources\AgentCreditBonusRules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AgentCreditBonusRulesTable
{
    public static function configure(Table $table): Table
    {
        /** @var array<int, string> $labels */
        $labels = config('agent_tiers.labels', []);

        return $table
            ->columns([
                TextColumn::make('tier')
                    ->label('Agent tier')
                    ->formatStateUsing(fn (int $state): string => $labels[$state] ?? (string) $state)
                    ->sortable(),
                TextColumn::make('rate')
                    ->label('Bonus rate')
                    ->numeric(decimalPlaces: 6)
                    ->sortable(),
                IconColumn::make('active')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
