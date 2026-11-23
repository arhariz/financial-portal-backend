<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExchangeRates\Pages;

use App\Filament\Resources\ExchangeRates\ExchangeRateResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateExchangeRate extends CreateRecord
{
    protected static string $resource = ExchangeRateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['set_by'] = Auth::id();

        return $data;
    }
}
