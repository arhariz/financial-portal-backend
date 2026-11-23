<?php

declare(strict_types=1);

namespace App\Filament\Resources\AgentCreditTransfers\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AgentCreditTransfersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('agent.name')
                    ->label('Agent')
                    ->searchable(),
                TextColumn::make('investor.name')
                    ->label('Investor')
                    ->searchable(),
                TextColumn::make('amount_usd')
                    ->label('USD')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
