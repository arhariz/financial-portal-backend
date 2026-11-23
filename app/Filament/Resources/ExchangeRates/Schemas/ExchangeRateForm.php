<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExchangeRates\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExchangeRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('bdt_per_usd')
                    ->label('BDT per USD')
                    ->required()
                    ->numeric()
                    ->minValue(0.0001)
                    ->step(0.0001),
                DatePicker::make('effective_from')
                    ->required()
                    ->default(now()->toDateString()),
            ]);
    }
}
