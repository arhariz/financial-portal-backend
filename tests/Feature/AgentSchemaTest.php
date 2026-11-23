<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\Agent;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AgentSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_tables_have_expected_columns(): void
    {
        $agentColumns = [
            'id', 'user_id', 'referral_code', 'tier', 'upline_agent_id',
            'status', 'total_sales', 'active_referrals_count', 'created_at',
        ];
        foreach ($agentColumns as $column) {
            $this->assertTrue(Schema::hasColumn('agents', $column), "Missing agents.{$column}");
        }

        $referralColumns = ['id', 'agent_id', 'referred_user_id', 'level', 'created_at'];
        foreach ($referralColumns as $column) {
            $this->assertTrue(Schema::hasColumn('referrals', $column), "Missing referrals.{$column}");
        }

        $this->assertFalse(Schema::hasColumn('agents', 'updated_at'));
        $this->assertFalse(Schema::hasColumn('referrals', 'updated_at'));
    }

    public function test_tier_is_an_integer_and_totals_are_decimal(): void
    {
        $this->assertContains(Schema::getColumnType('agents', 'tier'), ['integer', 'int', 'bigint', 'smallint']);
        $this->assertNotContains(Schema::getColumnType('agents', 'total_sales'), ['float', 'double', 'real']);

        $agent = Agent::factory()->create(['tier' => 2, 'total_sales' => '1234.5600']);

        $this->assertSame(2, $agent->fresh()->tier);
        $this->assertSame('1234.5600', $agent->fresh()->total_sales);
    }

    public function test_tier_display_labels_come_from_config_per_d031(): void
    {
        $this->assertSame([
            1 => 'Seed Investor',
            2 => 'Growth Investor',
            3 => 'Master Investor',
        ], config('agent_tiers.labels'));
    }

    public function test_agent_status_uses_the_existing_account_status_enum(): void
    {
        $this->assertSame(['active', 'suspended'], array_column(UserStatus::cases(), 'value'));

        $agent = Agent::factory()->create(['status' => UserStatus::Suspended]);

        $this->assertSame(UserStatus::Suspended, $agent->fresh()->status);
    }

    public function test_foreign_keys_and_filters_are_indexed(): void
    {
        $expected = [
            'agents' => ['user_id', 'referral_code', 'tier', 'upline_agent_id', 'status', 'created_at'],
            'referrals' => ['agent_id', 'referred_user_id', 'level', 'created_at'],
        ];

        foreach ($expected as $table => $columns) {
            $indexed = collect(Schema::getIndexes($table))
                ->flatMap(fn (array $index): array => $index['columns'])
                ->all();

            foreach ($columns as $column) {
                $this->assertContains($column, $indexed, "Missing index on {$table}.{$column}");
            }
        }
    }

    public function test_referral_code_is_unique(): void
    {
        $agent = Agent::factory()->create(['referral_code' => 'AGENT001']);

        $this->expectException(QueryException::class);

        Agent::factory()->create(['referral_code' => $agent->referral_code]);
    }
}
