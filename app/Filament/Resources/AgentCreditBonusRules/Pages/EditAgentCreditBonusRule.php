<?php

namespace App\Filament\Resources\AgentCreditBonusRules\Pages;

use App\Filament\Resources\AgentCreditBonusRules\AgentCreditBonusRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAgentCreditBonusRule extends EditRecord
{
    protected static string $resource = AgentCreditBonusRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
