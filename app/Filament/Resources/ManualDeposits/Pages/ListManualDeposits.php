<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManualDeposits\Pages;

use App\Filament\Resources\ManualDeposits\ManualDepositResource;
use Filament\Resources\Pages\ListRecords;

class ListManualDeposits extends ListRecords
{
    protected static string $resource = ManualDepositResource::class;

    /**
     * @return array<int, \Filament\Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
