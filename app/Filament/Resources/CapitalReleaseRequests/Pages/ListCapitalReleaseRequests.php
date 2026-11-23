<?php

declare(strict_types=1);

namespace App\Filament\Resources\CapitalReleaseRequests\Pages;

use App\Filament\Resources\CapitalReleaseRequests\CapitalReleaseRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListCapitalReleaseRequests extends ListRecords
{
    protected static string $resource = CapitalReleaseRequestResource::class;

    /**
     * @return array<int, \Filament\Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
