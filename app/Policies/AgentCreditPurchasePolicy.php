<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AgentCreditPurchase;
use App\Models\User;

class AgentCreditPurchasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('agent_credit.view');
    }

    public function view(User $user, AgentCreditPurchase $record): bool
    {
        return $user->can('agent_credit.view');
    }

    // Purchases are recorded only through the audited API service; the admin
    // table is read-only (FIL-05).
    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, AgentCreditPurchase $record): bool
    {
        return false;
    }

    public function delete(User $user, AgentCreditPurchase $record): bool
    {
        return false;
    }
}
