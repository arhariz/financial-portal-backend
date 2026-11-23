<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AgentCreditBonusRule;
use App\Models\User;

class AgentCreditBonusRulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('settings.manage');
    }

    public function view(User $user, AgentCreditBonusRule $record): bool
    {
        return $user->can('settings.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('settings.manage');
    }

    public function update(User $user, AgentCreditBonusRule $record): bool
    {
        return $user->can('settings.manage');
    }

    public function delete(User $user, AgentCreditBonusRule $record): bool
    {
        return $user->can('settings.manage');
    }
}
