<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_the_profile_create_page(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs(User::where('email', 'admin@forexbd.test')->firstOrFail())
            ->get('/admin/user-profiles/create')
            ->assertOk();
    }

    public function test_kyc_reviewer_can_view_profiles_but_not_create(): void
    {
        $this->seed(DatabaseSeeder::class);

        $kyc = User::where('email', 'kyc@forexbd.test')->firstOrFail();

        $this->actingAs($kyc)->get('/admin/user-profiles')->assertOk();
        $this->actingAs($kyc)->get('/admin/user-profiles/create')->assertForbidden();
    }

    public function test_support_cannot_view_profiles(): void
    {
        $this->seed(DatabaseSeeder::class);

        $support = User::where('email', 'support@forexbd.test')->firstOrFail();

        $this->actingAs($support)->get('/admin/user-profiles')->assertForbidden();
    }

    public function test_investor_cannot_reach_profiles_resource(): void
    {
        $this->seed(DatabaseSeeder::class);

        $investor = User::where('email', 'investor@forexbd.test')->firstOrFail();

        $response = $this->actingAs($investor)->get('/admin/user-profiles');
        $this->assertFalse($response->isSuccessful());
    }

    public function test_id_number_is_masked_in_listings(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'investor@forexbd.test')->firstOrFail();
        UserProfile::factory()->create([
            'user_id' => $user->id,
            'id_number' => 'SECRET-1234',
        ]);

        $admin = User::where('email', 'admin@forexbd.test')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/user-profiles')
            ->assertOk()
            ->assertSee('***1234')
            ->assertDontSee('SECRET-1234');
    }

    public function test_profile_creation_is_audit_logged(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@forexbd.test')->firstOrFail();
        $target = User::where('email', 'investor@forexbd.test')->firstOrFail();

        $this->actingAs($admin);

        UserProfile::create([
            'user_id' => $target->id,
            'full_name' => 'Test Person',
            'id_number' => 'ID-9999',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'user_profile.created',
        ]);
    }

    public function test_one_profile_per_user_is_enforced(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::where('email', 'investor@forexbd.test')->firstOrFail();

        UserProfile::factory()->create(['user_id' => $user->id]);

        $this->expectException(QueryException::class);

        UserProfile::factory()->create(['user_id' => $user->id]);
    }
}
