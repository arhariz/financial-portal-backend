<?php

declare(strict_types=1);

namespace App\Filament\Resources\AgentCreditTransfers;

use App\Filament\Resources\AgentCreditTransfers\Pages\ListAgentCreditTransfers;
use App\Filament\Resources\AgentCreditTransfers\Schemas\AgentCreditTransferForm;
use App\Filament\Resources\AgentCreditTransfers\Tables\AgentCreditTransfersTable;
use App\Models\AgentCreditTransfer;
use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AgentCreditTransferResource extends Resource
{
    protected static ?string $model = AgentCreditTransfer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $navigationLabel = 'Credit Transfers';

    protected static ?string $modelLabel = 'Credit Transfer';

    protected static ?string $pluralModelLabel = 'Credit Transfers';

    protected static ?int $navigationSort = 8;

    protected static string|UnitEnum|null $navigationGroup = 'Agents & Commissions';

    public static function getSlug(?Panel $panel = null): string
    {
        return 'agent-credit-transfers';
    }

    public static function form(Schema $schema): Schema
    {
        return AgentCreditTransferForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgentCreditTransfersTable::configure($table);
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListAgentCreditTransfers::route('/'),
        ];
    }
}
