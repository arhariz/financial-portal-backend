<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\KycStatus;
use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_hold_multiple_roles_at_once(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'support@forexbd.test')->firstOrFail();
        $user->assignRole('kyc_reviewer');

        $this->assertTrue($user->hasRole('support'));
        $this->assertTrue($user->hasRole('kyc_reviewer'));
        $this->assertGreaterThanOrEqual(2, $user->getRoleNames()->count());
    }

    public function test_assigning_a_second_role_does_not_remove_the_first(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'support@forexbd.test')->firstOrFail();
        $before = $user->getRoleNames()->sort()->values()->all();

        $user->assignRole('commission_manager');

        $this->assertSame($before, $user->fresh()->getRoleNames()->diff(['commission_manager'])->sort()->values()->all());
        $this->assertTrue($user->fresh()->hasRole('support'));
    }

    public function test_permissions_aggregate_across_assigned_roles(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'support@forexbd.test')->firstOrFail();
        $user->assignRole('finance_manager');

        $this->assertTrue($user->can('reports.view'));
        $this->assertTrue($user->can('withdrawals.approve'));
    }

    public function test_status_and_kyc_status_use_php_backed_enums(): void
    {
        $user = User::factory()->create([
            'status' => UserStatus::Suspended,
            'kyc_status' => KycStatus::Pending,
        ]);

        $fresh = $user->fresh();

        $this->assertSame(UserStatus::Suspended, $fresh->status);
        $this->assertSame(KycStatus::Pending, $fresh->kyc_status);
    }

    public function test_soft_deletes_are_available(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->deleted_at);

        $user->delete();

        $this->assertNotNull($user->fresh()->deleted_at);
        $this->assertSoftDeleted($user);
    }

    public function test_panel_access_is_permission_based_not_role_based(): void
    {
        $this->seed(DatabaseSeeder::class);

        $support = User::where('email', 'support@forexbd.test')->firstOrFail();
        $investor = User::where('email', 'investor@forexbd.test')->firstOrFail();

        $this->assertTrue($support->canAccessPanel(\Filament\Panel::make()->id('admin')));
        $this->assertFalse($investor->canAccessPanel(\Filament\Panel::make()->id('admin')));
    }
}
