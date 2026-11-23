<?php

declare(strict_types=1);

namespace App\Filament\Resources\AgentCreditTransfers\Pages;

use App\Filament\Resources\AgentCreditTransfers\AgentCreditTransferResource;
use Filament\Resources\Pages\ListRecords;

class ListAgentCreditTransfers extends ListRecords
{
    protected static string $resource = AgentCreditTransferResource::class;

    /**
     * @return array<int, \Filament\Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
