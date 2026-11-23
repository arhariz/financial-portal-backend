<?php

declare(strict_types=1);

namespace App\Filament\Resources\AgentCreditPurchases\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AgentCreditPurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Agent')
                    ->searchable(),
                TextColumn::make('amount_bdt')
                    ->label('BDT')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('amount_usd')
                    ->label('USD face value')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('bonus_amount_usd')
                    ->label('Role bonus (USD)')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('exchangeRate.bdt_per_usd')
                    ->label('Rate')
                    ->numeric(decimalPlaces: 4),
                TextColumn::make('creator.name')
                    ->label('Recorded by'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
