<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ExchangeRate;
use App\Models\User;

class ExchangeRatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('exchange_rates.view');
    }

    public function view(User $user, ExchangeRate $record): bool
    {
        return $user->can('exchange_rates.view');
    }

    public function create(User $user): bool
    {
        return $user->can('exchange_rates.manage');
    }

    public function update(User $user, ExchangeRate $record): bool
    {
        return false;
    }

    public function delete(User $user, ExchangeRate $record): bool
    {
        return false;
    }
}
