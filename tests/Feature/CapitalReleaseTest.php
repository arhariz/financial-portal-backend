<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CapitalReleaseStatus;
use App\Enums\KycStatus;
use App\Enums\TransactionDirection;
use App\Enums\UserStatus;
use App\Enums\WalletTransactionType;
use App\Enums\WalletType;
use App\Jobs\ReleaseCapital;
use App\Models\AuditLog;
use App\Models\CapitalReleaseRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\CapitalReleaseService;
use App\Services\LedgerReconciliationService;
use App\Services\WalletService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class CapitalReleaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The test queue driver is synchronous; the delayed release job must
        // be faked so the 24h gate is exercised deliberately in each test.
        Queue::fake();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function investor(): User
    {
        $investor = User::where('email', 'investor@forexbd.test')->firstOrFail();
        $investor->kyc_status = KycStatus::Approved;
        $investor->status = UserStatus::Active;
        $investor->save();

        return $investor;
    }

    private function admin(): User
    {
        return User::where('email', 'admin@forexbd.test')->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return ['amount_usd' => '100.0000'];
    }

    private function pendingRelease(User $investor): CapitalReleaseRequest
    {
        return CapitalReleaseRequest::factory()->create([
            'user_id' => $investor->id,
            'amount_usd' => '100.0000',
            'status' => CapitalReleaseStatus::Pending,
        ]);
    }

    public function test_kyc_approved_user_can_request_a_capital_release(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();

        $response = $this->actingAs($investor)->withHeader('Idempotency-Key', 'cr-001')
            ->postJson('/api/v1/capital-releases', $this->payload());

        $response->assertCreated()
            ->assertJsonPath('data.status', CapitalReleaseStatus::Pending->value)
            ->assertJsonPath('data.amount_usd', '100.0000');

        $release = CapitalReleaseRequest::firstOrFail();
        $this->assertSame($investor->id, $release->user_id);
        $this->assertSame('cr-001', $release->idempotency_key);
        $this->assertSame(1, AuditLog::where('action', 'capital_release.requested')->count());
    }

    public function test_duplicate_idempotency_key_returns_the_existing_request(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();

        $first = $this->actingAs($investor)->withHeader('Idempotency-Key', 'same-key')
            ->postJson('/api/v1/capital-releases', $this->payload());
        $second = $this->actingAs($investor)->withHeader('Idempotency-Key', 'same-key')
            ->postJson('/api/v1/capital-releases', $this->payload());

        $first->assertCreated();
        $second->assertOk()->assertJsonPath('data.id', $first->json('data.id'));
        $this->assertSame(1, CapitalReleaseRequest::count());
    }

    public function test_user_without_approved_kyc_cannot_request_release(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = User::where('email', 'investor@forexbd.test')->firstOrFail();
        $investor->kyc_status = KycStatus::None;
        $investor->save();

        $this->actingAs($investor)->withHeader('Idempotency-Key', 'no-kyc')
            ->postJson('/api/v1/capital-releases', $this->payload())->assertForbidden();

        $this->assertSame(0, CapitalReleaseRequest::count());
    }

    public function test_suspended_user_cannot_request_release(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        $investor->status = UserStatus::Suspended;
        $investor->save();

        $this->actingAs($investor)->withHeader('Idempotency-Key', 'suspended')
            ->postJson('/api/v1/capital-releases', $this->payload())->assertForbidden();

        $this->assertSame(0, CapitalReleaseRequest::count());
    }

    public function test_admin_can_view_the_capital_release_queue(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs($this->admin())->get('/admin/capital-releases')->assertOk();
    }

    public function test_support_cannot_view_the_capital_release_queue(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs(User::where('email', 'support@forexbd.test')->firstOrFail())
            ->get('/admin/capital-releases')->assertForbidden();
    }

    public function test_investor_cannot_view_the_capital_release_queue(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs($this->investor())->get('/admin/capital-releases')->assertForbidden();
    }

    public function test_approval_sets_a_24_hour_release_at_and_queues_the_job(): void
    {
        $this->seed(DatabaseSeeder::class);
        Carbon::setTestNow(Carbon::parse('2026-11-23 10:00:00'));
        Queue::fake();
        $release = $this->pendingRelease($this->investor());

        app(CapitalReleaseService::class)->approve($this->admin(), $release);

        $release->refresh();
        $this->assertSame(CapitalReleaseStatus::Approved, $release->status);
        $this->assertSame($this->admin()->id, $release->approved_by);
        $this->assertNotNull($release->approved_at);
        $this->assertSame('2026-11-24 10:00:00', $release->release_at?->toDateTimeString());
        Queue::assertPushed(ReleaseCapital::class, 1);
        Queue::assertPushed(ReleaseCapital::class, fn (ReleaseCapital $job): bool => $job->releaseId === $release->id);
        $this->assertSame(1, AuditLog::where('action', 'capital_release.approved')->count());
    }

    public function test_release_before_the_24_hour_gate_is_blocked(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        app(WalletService::class)->deposit($investor, WalletType::Investment, '100.0000');
        $release = $this->pendingRelease($investor);
        $service = app(CapitalReleaseService::class);
        Carbon::setTestNow(Carbon::parse('2026-11-23 10:00:00'));
        $service->approve($this->admin(), $release);

        Carbon::setTestNow(Carbon::parse('2026-11-24 09:59:59'));
        $blocked = false;
        try {
            $service->release($release->fresh() ?? $release);
        } catch (RuntimeException) {
            $blocked = true;
        }

        $this->assertTrue($blocked);
        $this->assertSame(CapitalReleaseStatus::Approved, $release->fresh()?->status);
        $this->assertSame(0, WalletTransaction::where('reference_type', CapitalReleaseRequest::class)->count());
    }

    public function test_release_after_24_hours_moves_investment_capital_to_cash_with_two_ledger_rows(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        app(WalletService::class)->deposit($investor, WalletType::Investment, '100.0000');
        $release = $this->pendingRelease($investor);
        $service = app(CapitalReleaseService::class);
        Carbon::setTestNow(Carbon::parse('2026-11-23 10:00:00'));
        $service->approve($this->admin(), $release);

        Carbon::setTestNow(Carbon::parse('2026-11-24 10:00:01'));
        $service->release($release->fresh() ?? $release);

        $release->refresh();
        $this->assertSame(CapitalReleaseStatus::Released, $release->status);
        $this->assertNotNull($release->released_at);
        $this->assertNotNull($release->wallet_transaction_id);

        $investment = Wallet::where('user_id', $investor->id)->where('type', WalletType::Investment)->firstOrFail();
        $cash = Wallet::where('user_id', $investor->id)->where('type', WalletType::Cash)->firstOrFail();
        $this->assertSame('0.0000', $investment->balance);
        $this->assertSame('100.0000', $cash->balance);

        $rows = WalletTransaction::where('reference_type', CapitalReleaseRequest::class)->get();
        $this->assertCount(2, $rows);
        $this->assertSame(1, $rows->where('direction', TransactionDirection::Debit)->count());
        $this->assertSame(1, $rows->where('direction', TransactionDirection::Credit)->count());
        $this->assertTrue($rows->every(fn (WalletTransaction $row): bool => $row->type === WalletTransactionType::CapitalRelease));
        $this->assertSame(0, app(LedgerReconciliationService::class)->reconcile()['breakCount']);
        $this->assertSame(1, AuditLog::where('action', 'capital_release.released')->count());
    }

    public function test_the_queued_job_executes_the_release(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        app(WalletService::class)->deposit($investor, WalletType::Investment, '100.0000');
        $release = $this->pendingRelease($investor);
        $service = app(CapitalReleaseService::class);
        Carbon::setTestNow(Carbon::parse('2026-11-23 10:00:00'));
        $service->approve($this->admin(), $release);

        Carbon::setTestNow(Carbon::parse('2026-11-24 10:00:01'));
        app(ReleaseCapital::class, ['releaseId' => $release->id])->handle($service);

        $this->assertSame(CapitalReleaseStatus::Released, $release->fresh()?->status);
        $this->assertSame('100.0000', Wallet::where('user_id', $investor->id)->where('type', WalletType::Cash)->firstOrFail()->balance);
    }

    public function test_releasing_twice_does_not_double_credit(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        app(WalletService::class)->deposit($investor, WalletType::Investment, '100.0000');
        $release = $this->pendingRelease($investor);
        $service = app(CapitalReleaseService::class);
        Carbon::setTestNow(Carbon::parse('2026-11-23 10:00:00'));
        $service->approve($this->admin(), $release);
        Carbon::setTestNow(Carbon::parse('2026-11-24 10:00:01'));
        $service->release($release->fresh() ?? $release);
        $service->release($release->fresh() ?? $release);

        $this->assertSame(2, WalletTransaction::where('reference_type', CapitalReleaseRequest::class)->count());
        $this->assertSame('100.0000', Wallet::where('user_id', $investor->id)->where('type', WalletType::Cash)->firstOrFail()->balance);
    }

    public function test_rejection_records_the_reason_and_queues_no_job(): void
    {
        $this->seed(DatabaseSeeder::class);
        Queue::fake();
        $release = $this->pendingRelease($this->investor());

        app(CapitalReleaseService::class)->reject($this->admin(), $release, 'Position is not eligible.');

        $release->refresh();
        $this->assertSame(CapitalReleaseStatus::Rejected, $release->status);
        $this->assertSame($this->admin()->id, $release->approved_by);
        Queue::assertNothingPushed();
        $audit = AuditLog::where('action', 'capital_release.rejected')->firstOrFail();
        $this->assertSame('Position is not eligible.', $audit->changes['rejection_reason']);
    }

    public function test_role_without_release_approval_permission_cannot_approve(): void
    {
        $this->seed(DatabaseSeeder::class);
        $release = $this->pendingRelease($this->investor());
        $support = User::where('email', 'support@forexbd.test')->firstOrFail();

        $denied = false;
        try {
            app(CapitalReleaseService::class)->approve($support, $release);
        } catch (AuthorizationException) {
            $denied = true;
        }

        $this->assertTrue($denied);
        $this->assertSame(CapitalReleaseStatus::Pending, $release->fresh()?->status);
    }
}
