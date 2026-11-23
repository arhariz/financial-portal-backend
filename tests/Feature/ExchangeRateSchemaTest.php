<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ExchangeRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExchangeRateSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_exchange_rates_table_has_expected_columns(): void
    {
        foreach (['id', 'bdt_per_usd', 'effective_from', 'set_by', 'created_at'] as $column) {
            $this->assertTrue(Schema::hasColumn('exchange_rates', $column), "Missing exchange_rates.{$column}");
        }

        $this->assertFalse(Schema::hasColumn('exchange_rates', 'updated_at'));
    }

    public function test_rate_column_is_decimal_and_not_float(): void
    {
        $type = Schema::getColumnType('exchange_rates', 'bdt_per_usd');

        $this->assertNotContains($type, ['float', 'double', 'real']);

        $rate = ExchangeRate::query()->create([
            'bdt_per_usd' => '118.4567',
            'effective_from' => '2026-11-23',
            'set_by' => User::factory()->create()->id,
        ]);

        $this->assertSame('118.4567', $rate->fresh()->bdt_per_usd);
    }

    public function test_history_and_foreign_key_columns_are_indexed(): void
    {
        $indexedColumns = collect(Schema::getIndexes('exchange_rates'))
            ->flatMap(fn (array $index): array => $index['columns'])
            ->all();

        $this->assertContains('effective_from', $indexedColumns);
        $this->assertContains('set_by', $indexedColumns);
        $this->assertContains('created_at', $indexedColumns);
    }

    public function test_set_by_is_a_restricting_foreign_key_to_users(): void
    {
        $foreignKeys = Schema::getForeignKeys('exchange_rates');

        $this->assertCount(1, $foreignKeys);
        $this->assertSame(['set_by'], $foreignKeys[0]['columns']);
        $this->assertSame('users', $foreignKeys[0]['foreign_table']);
        $this->assertSame('restrict', strtolower($foreignKeys[0]['on_delete']));
    }

    public function test_rate_remembers_the_admin_who_set_it_and_its_business_date(): void
    {
        $admin = User::factory()->create();

        $rate = ExchangeRate::query()->create([
            'bdt_per_usd' => '118.0000',
            'effective_from' => '2026-11-23',
            'set_by' => $admin->id,
        ]);

        $this->assertTrue($rate->setBy->is($admin));
        $this->assertSame('2026-11-23', $rate->effective_from->toDateString());
    }

    public function test_multiple_rates_are_retained_as_history(): void
    {
        $admin = User::factory()->create();

        foreach (['2026-11-22' => '117.0000', '2026-11-23' => '118.0000'] as $date => $amount) {
            ExchangeRate::query()->create([
                'bdt_per_usd' => $amount,
                'effective_from' => $date,
                'set_by' => $admin->id,
            ]);
        }

        $this->assertSame(2, ExchangeRate::query()->count());
    }
}
