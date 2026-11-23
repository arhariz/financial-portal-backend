<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExchangeRates;

use App\Filament\Resources\ExchangeRates\Pages\CreateExchangeRate;
use App\Filament\Resources\ExchangeRates\Pages\ListExchangeRates;
use App\Filament\Resources\ExchangeRates\Schemas\ExchangeRateForm;
use App\Filament\Resources\ExchangeRates\Tables\ExchangeRatesTable;
use App\Models\ExchangeRate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExchangeRateResource extends Resource
{
    protected static ?string $model = ExchangeRate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = 'Exchange Rates';

    protected static ?string $modelLabel = 'Exchange Rate';

    protected static ?string $pluralModelLabel = 'Exchange Rates';

    protected static ?int $navigationSort = 5;

    protected static string | \UnitEnum | null $navigationGroup = 'Config';

    public static function form(Schema $schema): Schema
    {
        return ExchangeRateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExchangeRatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExchangeRates::route('/'),
            'create' => CreateExchangeRate::route('/create'),
        ];
    }
}
