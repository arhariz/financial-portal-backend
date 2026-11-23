<?php

namespace App\Filament\Resources\AgentCreditBonusRules\Pages;

use App\Filament\Resources\AgentCreditBonusRules\AgentCreditBonusRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAgentCreditBonusRules extends ListRecords
{
    protected static string $resource = AgentCreditBonusRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
