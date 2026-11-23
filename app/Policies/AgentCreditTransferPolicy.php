<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AgentCreditTransfer;
use App\Models\User;

class AgentCreditTransferPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('agent_credit.view');
    }

    public function view(User $user, AgentCreditTransfer $record): bool
    {
        return $user->can('agent_credit.view');
    }

    // Transfers are created only via the audited agent API (FIL-05).
    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, AgentCreditTransfer $record): bool
    {
        return false;
    }

    public function delete(User $user, AgentCreditTransfer $record): bool
    {
        return false;
    }
}
