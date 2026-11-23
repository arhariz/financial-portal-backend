<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Referral;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registers_an_investor_without_referral_code(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'New Investor',
            'email' => 'new@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.email', 'new@example.com');

        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('investor'));
        $this->assertSame(0, Referral::count());
        $this->assertNull($user->referral_code_used);
    }

    public function test_registers_with_a_valid_referral_code_and_links_downline(): void
    {
        $this->seed(DatabaseSeeder::class);

        $agent = Agent::factory()->create(['referral_code' => 'REFCODE1']);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Referred Investor',
            'email' => 'referred@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'referral_code' => 'REFCODE1',
        ]);

        $response->assertCreated();

        $user = User::where('email', 'referred@example.com')->firstOrFail();
        $this->assertSame('REFCODE1', $user->referral_code_used);

        $referral = Referral::where('referred_user_id', $user->id)->firstOrFail();
        $this->assertSame($agent->id, $referral->agent_id);
        $this->assertSame(1, $referral->level);
    }

    public function test_rejects_an_unknown_referral_code(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Bad Code',
            'email' => 'badcode@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'referral_code' => 'DOESNOTEXIST',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['referral_code']);

        $this->assertSame(0, Referral::count());
    }

    public function test_rejects_duplicate_email(): void
    {
        $this->seed(DatabaseSeeder::class);

        User::factory()->create(['email' => 'dup@example.com']);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Dup',
            'email' => 'dup@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_requires_password_confirmation(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'No Confirm',
            'email' => 'noconfirm@example.com',
            'password' => 'Password@123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_requires_name_and_email(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->postJson('/api/v1/register', [
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);
    }
}
