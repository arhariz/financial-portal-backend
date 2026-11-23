<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExchangeRates\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExchangeRatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bdt_per_usd')
                    ->label('BDT per USD')
                    ->sortable(),
                TextColumn::make('effective_from')
                    ->date()
                    ->sortable(),
                TextColumn::make('setBy.name')
                    ->label('Set by'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('effective_from', 'desc');
    }
}
