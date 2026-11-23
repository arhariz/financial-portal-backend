<?php

declare(strict_types=1);

namespace App\Filament\Resources\AgentCreditBonusRules;

use App\Filament\Resources\AgentCreditBonusRules\Pages\CreateAgentCreditBonusRule;
use App\Filament\Resources\AgentCreditBonusRules\Pages\EditAgentCreditBonusRule;
use App\Filament\Resources\AgentCreditBonusRules\Pages\ListAgentCreditBonusRules;
use App\Filament\Resources\AgentCreditBonusRules\Schemas\AgentCreditBonusRuleForm;
use App\Filament\Resources\AgentCreditBonusRules\Tables\AgentCreditBonusRulesTable;
use App\Models\AgentCreditBonusRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AgentCreditBonusRuleResource extends Resource
{
    protected static ?string $model = AgentCreditBonusRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = 'Agent Credit Bonuses';

    protected static ?string $modelLabel = 'Agent Credit Bonus Rule';

    protected static ?string $pluralModelLabel = 'Agent Credit Bonus Rules';

    protected static ?int $navigationSort = 8;

    protected static string|UnitEnum|null $navigationGroup = 'Config';

    public static function form(Schema $schema): Schema
    {
        return AgentCreditBonusRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgentCreditBonusRulesTable::configure($table);
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListAgentCreditBonusRules::route('/'),
            'create' => CreateAgentCreditBonusRule::route('/create'),
            'edit' => EditAgentCreditBonusRule::route('/{record}/edit'),
        ];
    }
}
