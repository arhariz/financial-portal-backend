<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ExchangeRate;
use App\Models\User;
use App\Services\ExchangeRateService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExchangeRateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_list_and_create_pages(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@forexbd.test')->firstOrFail();

        $this->actingAs($admin)->get('/admin/exchange-rates')->assertOk();
        $this->actingAs($admin)->get('/admin/exchange-rates/create')->assertOk();
    }

    public function test_support_cannot_view_exchange_rates(): void
    {
        $this->seed(DatabaseSeeder::class);

        $support = User::where('email', 'support@forexbd.test')->firstOrFail();

        $this->actingAs($support)->get('/admin/exchange-rates')->assertForbidden();
    }

    public function test_investor_cannot_reach_exchange_rates(): void
    {
        $this->seed(DatabaseSeeder::class);

        $investor = User::where('email', 'investor@forexbd.test')->firstOrFail();

        $response = $this->actingAs($investor)->get('/admin/exchange-rates');
        $this->assertFalse($response->isSuccessful());
    }

    public function test_creating_a_rate_appends_history_without_overwriting(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@forexbd.test')->firstOrFail();
        $service = app(ExchangeRateService::class);

        $first = $service->setRate($admin, '118.0000', now()->subDay());
        $second = $service->setRate($admin, '120.5000', now());

        $this->assertSame(2, ExchangeRate::count());
        $this->assertNotSame($first->id, $second->id);
        $this->assertSame('120.5000', $second->bdt_per_usd);
        $this->assertSame('118.0000', $first->fresh()->bdt_per_usd);
    }

    public function test_rate_is_stored_at_four_decimal_places(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@forexbd.test')->firstOrFail();
        $rate = app(ExchangeRateService::class)->setRate($admin, '117.2500', now());

        $this->assertSame('117.2500', $rate->fresh()->bdt_per_usd);
    }

    public function test_current_returns_the_latest_effective_rate(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@forexbd.test')->firstOrFail();
        $service = app(ExchangeRateService::class);

        $service->setRate($admin, '110.0000', now()->subDays(2));
        $latest = $service->setRate($admin, '120.0000', now());

        $this->assertSame($latest->id, $service->current()->id);
        $this->assertSame('120.0000', $service->current()->bdt_per_usd);
    }
}
