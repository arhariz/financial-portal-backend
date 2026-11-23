<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PositionSource;
use App\Enums\PositionStatus;
use App\Enums\SymbolCategory;
use App\Enums\SymbolStatus;
use App\Models\Symbol;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InvestmentSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_investment_tables_have_expected_columns(): void
    {
        $symbolColumns = [
            'id', 'code', 'name', 'category', 'status', 'min_investment', 'created_by',
            'created_at', 'updated_at',
        ];
        foreach ($symbolColumns as $column) {
            $this->assertTrue(Schema::hasColumn('symbols', $column), "Missing symbols.{$column}");
        }

        $positionColumns = [
            'id', 'user_id', 'symbol_id', 'capital', 'original_amount', 'investment_date',
            'eligible_date', 'status', 'source', 'created_at', 'updated_at',
        ];
        foreach ($positionColumns as $column) {
            $this->assertTrue(Schema::hasColumn('positions', $column), "Missing positions.{$column}");
        }
    }

    public function test_money_columns_are_decimal_and_not_float(): void
    {
        $columns = [
            ['symbols', 'min_investment'],
            ['positions', 'capital'],
            ['positions', 'original_amount'],
        ];
        foreach ($columns as [$table, $column]) {
            $this->assertNotContains(Schema::getColumnType($table, $column), ['float', 'double', 'real']);
        }

        $symbol = Symbol::factory()->create(['min_investment' => '10.5000']);

        $this->assertSame('10.5000', $symbol->fresh()->min_investment);
    }

    public function test_status_enums_match_schema_registry(): void
    {
        $this->assertSame(['metal', 'crypto', 'energy', 'forex'], array_column(SymbolCategory::cases(), 'value'));
        $this->assertSame(['active', 'paused'], array_column(SymbolStatus::cases(), 'value'));
        $this->assertSame(['active', 'closed'], array_column(PositionStatus::cases(), 'value'));
        // WAL-05 allows commission → investment, so the position must record
        // that origin too (GOV-02 correction logged in D-093).
        $this->assertSame(['cash', 'profit', 'commission'], array_column(PositionSource::cases(), 'value'));
    }

    public function test_eligibility_date_is_a_date_column_and_is_indexed(): void
    {
        $this->assertSame('date', Schema::getColumnType('positions', 'eligible_date'));

        $indexed = collect(Schema::getIndexes('positions'))
            ->flatMap(fn (array $index): array => $index['columns'])
            ->all();

        $this->assertContains('eligible_date', $indexed);
        $this->assertContains('investment_date', $indexed);
    }

    public function test_all_foreign_keys_and_filters_are_indexed(): void
    {
        $expected = [
            'symbols' => ['code', 'status', 'created_by', 'created_at'],
            'positions' => ['user_id', 'symbol_id', 'status', 'source', 'created_at'],
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

    public function test_symbol_code_is_unique(): void
    {
        $symbol = Symbol::factory()->create(['code' => 'XAUUSD']);

        $this->expectException(QueryException::class);

        Symbol::factory()->create(['code' => $symbol->code]);
    }
}
