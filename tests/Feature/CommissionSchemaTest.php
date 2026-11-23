<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CommissionEvent;
use App\Enums\CommissionStatus;
use App\Enums\DeductionType;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\DeductionProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Tests\TestCase;

class CommissionSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_commission_tables_have_expected_columns(): void
    {
        $expected = [
            'deduction_profiles' => ['id', 'name', 'rules', 'created_by', 'created_at', 'updated_at'],
            'commission_rules' => ['id', 'event', 'agent_tier', 'level', 'rate', 'enabled', 'holding_period_hours', 'deduction_profile_id', 'created_at', 'updated_at'],
            'commissions' => ['id', 'agent_id', 'rule_id', 'event', 'source_user_id', 'source_reference_type', 'source_reference_id', 'base_amount', 'amount', 'status', 'vest_at', 'wallet_transaction_id', 'created_at', 'updated_at'],
        ];

        foreach ($expected as $table => $columns) {
            foreach ($columns as $column) {
                $this->assertTrue(Schema::hasColumn($table, $column), "Missing {$table}.{$column}");
            }
        }
    }

    public function test_decimal_columns_round_trip_at_the_locked_scale(): void
    {
        $rule = CommissionRule::factory()->create(['rate' => '0.123456']);
        $commission = Commission::factory()->create([
            'base_amount' => '1234.5600',
            'amount' => '61.7280',
        ]);

        $this->assertSame('0.123456', $rule->fresh()->rate);
        $this->assertSame('1234.5600', $commission->fresh()->base_amount);
        $this->assertSame('61.7280', $commission->fresh()->amount);

        $this->assertNotContains(Schema::getColumnType('commission_rules', 'rate'), ['float', 'double', 'real']);
        $this->assertNotContains(Schema::getColumnType('commissions', 'base_amount'), ['float', 'double', 'real']);
        $this->assertNotContains(Schema::getColumnType('commissions', 'amount'), ['float', 'double', 'real']);
    }

    public function test_enums_match_the_locked_values(): void
    {
        $this->assertSame(
            ['investment', 'profit_distribution', 'credit_purchase'],
            array_column(CommissionEvent::cases(), 'value')
        );
        $this->assertSame(
            ['pending', 'vested', 'cancelled', 'reversed'],
            array_column(CommissionStatus::cases(), 'value')
        );
        $this->assertSame(
            ['loss_adjustment', 'reversal', 'admin_adjustment'],
            array_column(DeductionType::cases(), 'value')
        );
    }

    public function test_default_deduction_types_are_seeded_per_d030(): void
    {
        /** @var list<array{name: string, rules: array<string, mixed>}> $defaults */
        $defaults = config('deduction_types.defaults');

        $this->assertSame(
            array_column(DeductionType::cases(), 'value'),
            array_column($defaults, 'name')
        );
        $this->assertSame(
            ['admin_adjustment', 'loss_adjustment', 'reversal'],
            DB::table('deduction_profiles')->orderBy('name')->pluck('name')->all()
        );

        $profile = DeductionProfile::where('name', 'admin_adjustment')->firstOrFail();
        $this->assertSame(true, $profile->rules['requires_note']);
    }

    public function test_foreign_keys_and_filters_are_indexed(): void
    {
        $expected = [
            'deduction_profiles' => ['name', 'created_by', 'created_at'],
            'commission_rules' => ['event', 'agent_tier', 'level', 'enabled', 'deduction_profile_id', 'created_at'],
            'commissions' => ['agent_id', 'rule_id', 'event', 'source_user_id', 'source_reference_type', 'source_reference_id', 'status', 'vest_at', 'wallet_transaction_id', 'created_at'],
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

    public function test_commission_rows_reject_updates(): void
    {
        $commission = Commission::factory()->create();

        $this->expectException(LogicException::class);

        $commission->setAttribute('amount', '9.0000');
        $commission->save();
    }

    public function test_commission_rows_reject_deletes(): void
    {
        $commission = Commission::factory()->create();

        $this->expectException(LogicException::class);

        $commission->delete();
    }
}
