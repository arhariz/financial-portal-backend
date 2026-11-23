<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExchangeRate;
use App\Models\User;
use Illuminate\Support\Carbon;

final class ExchangeRateService
{
    public function setRate(User $actor, string $rate, Carbon $effectiveFrom): ExchangeRate
    {
        return ExchangeRate::create([
            'bdt_per_usd' => $rate,
            'effective_from' => $effectiveFrom->toDateString(),
            'set_by' => $actor->id,
        ]);
    }

    public function current(): ?ExchangeRate
    {
        return ExchangeRate::query()
            ->where('effective_from', '<=', now()->toDateString())
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();
    }
}
