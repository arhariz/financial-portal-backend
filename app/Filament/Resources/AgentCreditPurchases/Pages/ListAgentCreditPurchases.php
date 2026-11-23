<?php

declare(strict_types=1);

namespace App\Filament\Resources\AgentCreditPurchases\Pages;

use App\Filament\Resources\AgentCreditPurchases\AgentCreditPurchaseResource;
use Filament\Resources\Pages\ListRecords;

class ListAgentCreditPurchases extends ListRecords
{
    protected static string $resource = AgentCreditPurchaseResource::class;

    /**
     * @return array<int, \Filament\Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
