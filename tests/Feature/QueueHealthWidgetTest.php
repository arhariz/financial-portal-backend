<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PnlDistributionStatus;
use App\Enums\SymbolStatus;
use App\Filament\Widgets\PnlStatus;
use App\Filament\Widgets\QueueHealth;
use App\Models\PnlDistribution;
use App\Models\Symbol;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class QueueHealthWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function authenticateAsAdmin(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('email', 'admin@forexbd.test')->firstOrFail());
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function insertJob(array $overrides = []): void
    {
        $now = time();

        DB::table('jobs')->insert(array_merge([
            'queue' => 'default',
            'payload' => '{}',
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => $now,
            'created_at' => $now,
        ], $overrides));
    }

    private function insertFailedJob(): void
    {
        DB::table('failed_jobs')->insert([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'connection' => 'database',
            'queue' => 'capital-releases',
            'payload' => '{}',
            'exception' => 'boom',
            'failed_at' => now(),
        ]);
    }

    public function test_queue_health_is_green_with_no_jobs(): void
    {
        $this->authenticateAsAdmin();

        Livewire::test(QueueHealth::class)
            ->assertSee('Queue Health')
            ->assertSee('Pending jobs')
            ->assertSee('Failed jobs')
            ->assertSee('GREEN')
            ->assertSee('P&L Operator');
    }

    public function test_queue_health_is_amber_when_oldest_job_is_between_five_and_thirty_minutes(): void
    {
        $this->authenticateAsAdmin();
        $this->insertJob(['created_at' => time() - 600]);

        Livewire::test(QueueHealth::class)->assertSee('AMBER');
    }

    public function test_queue_health_is_red_for_failed_jobs_or_a_job_older_than_thirty_minutes(): void
    {
        $this->authenticateAsAdmin();
        $this->insertFailedJob();

        Livewire::test(QueueHealth::class)->assertSee('RED');
    }

    public function test_queue_health_state_follows_live_job_data(): void
    {
        $this->authenticateAsAdmin();
        $this->insertJob();

        Livewire::test(QueueHealth::class)->assertSee('GREEN');

        DB::table('jobs')->update(['created_at' => time() - 3600]);

        Livewire::test(QueueHealth::class)->assertSee('RED');
    }

    public function test_pnl_status_shows_an_honest_empty_state_without_wp5_t05(): void
    {
        $this->authenticateAsAdmin();
        $this->assertSame(0, PnlDistribution::count());

        Livewire::test(PnlStatus::class)
            ->assertSee('P&L Distribution')
            ->assertSee('No distributions posted yet')
            ->assertSee('WP5-T05')
            ->assertSee('AMBER');
    }

    public function test_pnl_status_uses_the_latest_real_trade_date(): void
    {
        $this->authenticateAsAdmin();
        Carbon::setTestNow(Carbon::parse('2026-11-23 12:00:00'));
        $symbol = Symbol::factory()->create(['status' => SymbolStatus::Active]);
        PnlDistribution::factory()->create([
            'symbol_id' => $symbol->id,
            'trade_date' => '2026-11-22',
            'status' => PnlDistributionStatus::Distributed,
        ]);

        Livewire::test(PnlStatus::class)
            ->assertSee('2026-11-22')
            ->assertSee('P&L Operator');
    }

    public function test_widgets_follow_reports_view_permission(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs(User::where('email', 'support@forexbd.test')->firstOrFail());
        $this->assertTrue(QueueHealth::canView());
        $this->assertTrue(PnlStatus::canView());

        $this->actingAs(User::where('email', 'investor@forexbd.test')->firstOrFail());
        $this->assertFalse(QueueHealth::canView());
        $this->assertFalse(PnlStatus::canView());
    }
}
