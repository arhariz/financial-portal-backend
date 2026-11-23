<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManualDeposits;

use App\Filament\Resources\ManualDeposits\Pages\ListManualDeposits;
use App\Filament\Resources\ManualDeposits\Schemas\ManualDepositForm;
use App\Filament\Resources\ManualDeposits\Tables\ManualDepositsTable;
use App\Models\ManualDeposit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ManualDepositResource extends Resource
{
    protected static ?string $model = ManualDeposit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = 'Deposits';

    protected static ?string $modelLabel = 'Deposit';

    protected static ?string $pluralModelLabel = 'Deposits';

    protected static ?int $navigationSort = 4;

    protected static string|UnitEnum|null $navigationGroup = 'Money Flow';

    public static function form(Schema $schema): Schema
    {
        return ManualDepositForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ManualDepositsTable::configure($table);
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListManualDeposits::route('/'),
        ];
    }
}
