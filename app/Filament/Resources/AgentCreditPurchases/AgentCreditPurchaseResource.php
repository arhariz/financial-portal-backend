<?php

declare(strict_types=1);

namespace App\Filament\Resources\AgentCreditPurchases;

use App\Filament\Resources\AgentCreditPurchases\Pages\ListAgentCreditPurchases;
use App\Filament\Resources\AgentCreditPurchases\Schemas\AgentCreditPurchaseForm;
use App\Filament\Resources\AgentCreditPurchases\Tables\AgentCreditPurchasesTable;
use App\Models\AgentCreditPurchase;
use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AgentCreditPurchaseResource extends Resource
{
    protected static ?string $model = AgentCreditPurchase::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = 'Agent Credits';

    protected static ?string $modelLabel = 'Agent Credit Purchase';

    protected static ?string $pluralModelLabel = 'Agent Credit Purchases';

    protected static ?int $navigationSort = 7;

    protected static string|UnitEnum|null $navigationGroup = 'Agents & Commissions';

    public static function getSlug(?Panel $panel = null): string
    {
        return 'agent-credits';
    }

    public static function form(Schema $schema): Schema
    {
        return AgentCreditPurchaseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgentCreditPurchasesTable::configure($table);
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListAgentCreditPurchases::route('/'),
        ];
    }
}
