<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\KycStatus;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

final class GuardService
{
    public function canInvest(User $user): bool
    {
        return $this->isKycApproved($user) && $this->isActive($user);
    }

    public function canWithdraw(User $user): bool
    {
        return $this->isKycApproved($user) && $this->isActive($user);
    }

    public function ensureCanInvest(User $user): void
    {
        if (! $this->canInvest($user)) {
            throw new AuthorizationException('KYC approval and an active account are required to invest.');
        }
    }

    public function ensureCanWithdraw(User $user): void
    {
        if (! $this->canWithdraw($user)) {
            throw new AuthorizationException('KYC approval and an active account are required to withdraw.');
        }
    }

    private function isKycApproved(User $user): bool
    {
        return $user->kyc_status === KycStatus::Approved;
    }

    private function isActive(User $user): bool
    {
        return $user->status === UserStatus::Active;
    }
}
