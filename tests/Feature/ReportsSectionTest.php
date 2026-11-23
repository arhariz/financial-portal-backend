<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\Reports;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsSectionTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    private const TAB_LABELS = [
        'Overview',
        'Ledger & Wallets',
        'Investment & P&L',
        'Agents & Commissions',
        'KYC & Security',
        'Money Flow',
        'Operations',
        'Exports',
    ];

    public function test_super_admin_sees_all_eight_tabs(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->actingAs(User::where('email', 'admin@forexbd.test')->firstOrFail())
            ->get('/admin/reports');

        $response->assertOk();
        foreach (self::TAB_LABELS as $label) {
            $response->assertSee($label);
        }
    }

    public function test_support_has_view_only_access_to_reports(): void
    {
        $this->seed(DatabaseSeeder::class);

        $support = User::where('email', 'support@forexbd.test')->firstOrFail();

        $this->assertTrue($support->can('reports.view'));
        $this->assertFalse($support->can('users.manage'));
        $this->actingAs($support)->get('/admin/reports')->assertOk();
    }

    public function test_investor_cannot_reach_the_reports_page(): void
    {
        $this->seed(DatabaseSeeder::class);

        $investor = User::where('email', 'investor@forexbd.test')->firstOrFail();
        $response = $this->actingAs($investor)->get('/admin/reports');

        $this->assertFalse($response->isSuccessful());
    }

    public function test_page_authorization_uses_can_and_denies_investors(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs(User::where('email', 'support@forexbd.test')->firstOrFail());
        $this->assertTrue(Reports::canAccess());

        $this->actingAs(User::where('email', 'investor@forexbd.test')->firstOrFail());
        $this->assertFalse(Reports::canAccess());
    }

    public function test_page_is_grouped_under_reports_with_expected_url(): void
    {
        $this->assertSame('Reports', Reports::getNavigationGroup());
        $this->assertStringEndsWith('/admin/reports', Reports::getUrl());
    }
}
