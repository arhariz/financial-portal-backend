<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PnlDistributionStatus;
use App\Models\PnlDistribution;
use App\Models\PnlDistributionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DistributionSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_distribution_tables_have_expected_columns(): void
    {
        $distributionColumns = [
            'id', 'symbol_id', 'trade_date', 'profit_loss', 'entry_by', 'status',
            'distributed_at', 'reversal_of_id', 'created_at', 'updated_at',
        ];
        foreach ($distributionColumns as $column) {
            $this->assertTrue(Schema::hasColumn('pnl_distributions', $column), "Missing pnl_distributions.{$column}");
        }

        $itemColumns = [
            'id', 'distribution_id', 'position_id', 'user_id', 'share_ratio',
            'amount', 'wallet_transaction_id', 'created_at',
        ];
        foreach ($itemColumns as $column) {
            $this->assertTrue(Schema::hasColumn('pnl_distribution_items', $column), "Missing pnl_distribution_items.{$column}");
        }

        $this->assertFalse(Schema::hasColumn('pnl_distribution_items', 'updated_at'));
    }

    public function test_money_columns_are_decimal_and_not_float(): void
    {
        $this->assertNotContains(Schema::getColumnType('pnl_distributions', 'profit_loss'), ['float', 'double', 'real']);
        $this->assertNotContains(Schema::getColumnType('pnl_distribution_items', 'share_ratio'), ['float', 'double', 'real']);
        $this->assertNotContains(Schema::getColumnType('pnl_distribution_items', 'amount'), ['float', 'double', 'real']);

        $distribution = PnlDistribution::factory()->create(['profit_loss' => '-110.0000']);
        $item = PnlDistributionItem::factory()->create([
            'distribution_id' => $distribution->id,
            'share_ratio' => '0.500000',
            'amount' => '-10.0000',
        ]);

        $this->assertSame('-110.0000', $distribution->fresh()->profit_loss);
        $this->assertSame('0.500000', $item->fresh()->share_ratio);
        $this->assertSame('-10.0000', $item->fresh()->amount);
    }

    public function test_status_enum_matches_schema_registry(): void
    {
        $this->assertSame(['draft', 'distributed', 'reversed'], array_column(PnlDistributionStatus::cases(), 'value'));
    }

    public function test_foreign_keys_and_date_filters_are_indexed(): void
    {
        $expected = [
            'pnl_distributions' => ['symbol_id', 'trade_date', 'status', 'entry_by', 'reversal_of_id', 'created_at'],
            'pnl_distribution_items' => ['distribution_id', 'position_id', 'user_id', 'wallet_transaction_id', 'created_at'],
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

    public function test_reversal_references_the_original_distribution(): void
    {
        $original = PnlDistribution::factory()->create();
        $reversal = PnlDistribution::factory()->create([
            'reversal_of_id' => $original->id,
            'status' => PnlDistributionStatus::Reversed,
        ]);

        $this->assertTrue($reversal->reversalOf->is($original));
    }

    public function test_distribution_items_sum_exactly_to_the_posted_amount(): void
    {
        $distribution = PnlDistribution::factory()->create(['profit_loss' => '100.0000']);

        foreach (['50.0000', '30.0000', '20.0000'] as $amount) {
            PnlDistributionItem::factory()->create([
                'distribution_id' => $distribution->id,
                'amount' => $amount,
            ]);
        }

        $sumScaled = PnlDistributionItem::query()
            ->where('distribution_id', $distribution->id)
            ->pluck('amount')
            ->reduce(fn (int $carry, string $amount): int => $carry + (int) str_replace('.', '', $amount), 0);

        $this->assertSame(1000000, $sumScaled);
    }
}
