<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\KycStatus;
use App\Enums\UserStatus;
use App\Models\User;
use App\Services\GuardService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_active_user_may_invest_and_withdraw(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::Active,
            'kyc_status' => KycStatus::Approved,
        ]);

        $guard = app(GuardService::class);

        $this->assertTrue($guard->canInvest($user));
        $this->assertTrue($guard->canWithdraw($user));
    }

    public function test_pending_kyc_blocks_both_actions(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::Active,
            'kyc_status' => KycStatus::Pending,
        ]);

        $guard = app(GuardService::class);

        $this->assertFalse($guard->canInvest($user));
        $this->assertFalse($guard->canWithdraw($user));

        $this->expectException(AuthorizationException::class);
        $guard->ensureCanInvest($user);
    }

    public function test_rejected_kyc_blocks_both_actions(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::Active,
            'kyc_status' => KycStatus::Rejected,
        ]);

        $guard = app(GuardService::class);

        $this->assertFalse($guard->canInvest($user));
        $this->assertFalse($guard->canWithdraw($user));
    }

    public function test_suspension_blocks_even_with_approved_kyc(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::Suspended,
            'kyc_status' => KycStatus::Approved,
        ]);

        $guard = app(GuardService::class);

        $this->assertFalse($guard->canInvest($user));
        $this->assertFalse($guard->canWithdraw($user));

        $this->expectException(AuthorizationException::class);
        $guard->ensureCanWithdraw($user);
    }

    public function test_suspension_with_pending_kyc_is_still_blocked(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::Suspended,
            'kyc_status' => KycStatus::Pending,
        ]);

        $guard = app(GuardService::class);

        $this->assertFalse($guard->canInvest($user));
        $this->assertFalse($guard->canWithdraw($user));
    }

    public function test_none_kyc_status_is_not_approved(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::Active,
            'kyc_status' => KycStatus::None,
        ]);

        $guard = app(GuardService::class);

        $this->assertFalse($guard->canInvest($user));
        $this->assertFalse($guard->canWithdraw($user));
    }
}
