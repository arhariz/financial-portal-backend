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

    /**
     * Deposits are allowed before KYC approval (plan §2), but a suspended
     * account is blocked from every money action (SEC-03).
     */
    public function ensureCanDeposit(User $user): void
    {
        if (! $this->isActive($user)) {
            throw new AuthorizationException('An active account is required to submit a deposit.');
        }
    }

    /**
     * Internal wallet transfers are money actions: KYC approval and an
     * active account are required (SEC-02/03).
     */
    public function ensureCanTransfer(User $user): void
    {
        if (! $this->canInvest($user)) {
            throw new AuthorizationException('KYC approval and an active account are required for transfers.');
        }
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
