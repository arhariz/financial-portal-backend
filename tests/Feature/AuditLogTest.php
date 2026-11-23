<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\LoginHistory;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_login_records_login_history_and_audit_log(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->withHeaders(['User-Agent' => 'TestAgent/1.0'])
            ->postJson('/api/v1/login', [
                'email' => 'admin@forexbd.test',
                'password' => 'Password@123',
            ])->assertOk();

        $user = User::where('email', 'admin@forexbd.test')->firstOrFail();

        $this->assertSame(1, LoginHistory::count());
        $history = LoginHistory::firstOrFail();
        $this->assertSame($user->id, $history->user_id);
        $this->assertSame('127.0.0.1', $history->ip);
        $this->assertSame('TestAgent/1.0', $history->user_agent);
        $this->assertNotEmpty($history->logged_in_at);
        $this->assertNull($history->logged_out_at);

        $this->assertSame(1, AuditLog::count());
        $log = AuditLog::firstOrFail();
        $this->assertSame($user->id, $log->actor_id);
        $this->assertSame('auth.login', $log->action);
        $this->assertSame('127.0.0.1', $log->ip);
        $this->assertSame('TestAgent/1.0', $log->user_agent);
        $this->assertNotEmpty($log->changes);
    }

    public function test_failed_login_records_an_audit_entry_without_login_history(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->withHeaders(['User-Agent' => 'TestAgent/1.0'])
            ->postJson('/api/v1/login', [
                'email' => 'admin@forexbd.test',
                'password' => 'wrong-password',
            ])->assertUnauthorized();

        $this->assertSame(0, LoginHistory::count());
        $this->assertSame(1, AuditLog::count());

        $log = AuditLog::firstOrFail();
        $this->assertSame('auth.login_failed', $log->action);
        $this->assertSame('127.0.0.1', $log->ip);
        $this->assertSame('TestAgent/1.0', $log->user_agent);
        $this->assertSame('admin@forexbd.test', $log->changes['email'] ?? null);
    }

    public function test_logout_updates_the_open_login_history_entry(): void
    {
        $this->seed(DatabaseSeeder::class);

        $token = $this->postJson('/api/v1/login', [
            'email' => 'admin@forexbd.test',
            'password' => 'Password@123',
        ])->json('token');

        $this->postJson('/api/v1/logout', [], ['Authorization' => 'Bearer '.$token])
            ->assertOk();

        $history = LoginHistory::firstOrFail();
        $this->assertNotNull($history->logged_out_at);

        $this->assertSame(2, AuditLog::count());
        $this->assertSame('auth.logout', AuditLog::latest('id')->firstOrFail()->action);
    }

    public function test_audit_log_rows_are_immutable(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->postJson('/api/v1/login', [
            'email' => 'admin@forexbd.test',
            'password' => 'Password@123',
        ])->assertOk();

        $log = AuditLog::firstOrFail();

        $this->expectException(\LogicException::class);
        $log->setAttribute('action', 'tampered');
        $log->save();
    }

    public function test_audit_log_rows_cannot_be_deleted(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->postJson('/api/v1/login', [
            'email' => 'admin@forexbd.test',
            'password' => 'Password@123',
        ])->assertOk();

        $log = AuditLog::firstOrFail();

        $this->expectException(\LogicException::class);
        $log->delete();
    }

    public function test_audit_changes_round_trip_as_json(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->postJson('/api/v1/login', [
            'email' => 'admin@forexbd.test',
            'password' => 'Password@123',
        ])->assertOk();

        $log = AuditLog::firstOrFail();
        $log->refresh();

        $this->assertNotEmpty($log->changes);
        $this->assertArrayHasKey('guard', $log->changes);
    }
}
