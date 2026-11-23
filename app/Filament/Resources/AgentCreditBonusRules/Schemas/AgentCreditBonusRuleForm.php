<?php

declare(strict_types=1);

namespace App\Filament\Resources\AgentCreditBonusRules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AgentCreditBonusRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        /** @var array<int, string> $labels */
        $labels = config('agent_tiers.labels', []);

        return $schema
            ->components([
                Select::make('tier')
                    ->label('Agent tier')
                    ->options($labels)
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('rate')
                    ->label('Bonus rate (fraction of USD face value)')
                    ->helperText('0.050000 = 5% of the credited USD face value. No default is provided.')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->rules(['regex:/^\d+(\.\d{1,6})?$/']),
                Toggle::make('active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
