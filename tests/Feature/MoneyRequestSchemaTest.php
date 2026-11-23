<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CapitalReleaseStatus;
use App\Enums\ManualDepositStatus;
use App\Enums\WithdrawalSourceWalletType;
use App\Enums\WithdrawalStatus;
use App\Models\ManualDeposit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MoneyRequestSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_money_request_tables_have_expected_columns(): void
    {
        $expected = [
            'manual_deposits' => [
                'id', 'user_id', 'amount_usd', 'amount_bdt', 'exchange_rate_id',
                'payment_method', 'proof_path', 'status', 'reviewed_by', 'reviewed_at',
                'rejection_reason', 'wallet_transaction_id', 'created_at', 'updated_at',
            ],
            'withdrawal_requests' => [
                'id', 'user_id', 'source_wallet_type', 'amount_usd', 'payout_method',
                'payout_details', 'status', 'requested_at', 'approved_by', 'approved_at',
                'paid_at', 'rejection_reason', 'wallet_transaction_id',
            ],
            'capital_release_requests' => [
                'id', 'user_id', 'amount_usd', 'status', 'requested_at', 'approved_by',
                'approved_at', 'release_at', 'released_at', 'wallet_transaction_id',
            ],
        ];

        foreach ($expected as $table => $columns) {
            foreach ($columns as $column) {
                $this->assertTrue(Schema::hasColumn($table, $column), "Missing {$table}.{$column}");
            }
        }

        $this->assertFalse(Schema::hasColumn('withdrawal_requests', 'updated_at'));
        $this->assertFalse(Schema::hasColumn('capital_release_requests', 'updated_at'));
        $this->assertTrue(Schema::hasColumn('manual_deposits', 'updated_at'));
    }

    public function test_usd_amounts_are_decimal_and_not_float(): void
    {
        foreach (['manual_deposits', 'withdrawal_requests', 'capital_release_requests'] as $table) {
            $type = Schema::getColumnType($table, 'amount_usd');

            $this->assertNotContains($type, ['float', 'double', 'real'], "Float column on {$table}");
        }

        $deposit = ManualDeposit::factory()->create(['amount_usd' => '123.4567']);

        $this->assertSame('123.4567', $deposit->fresh()->amount_usd);
    }

    public function test_status_enums_match_schema_registry(): void
    {
        $this->assertSame(['pending', 'approved', 'rejected'], array_column(ManualDepositStatus::cases(), 'value'));
        $this->assertSame(['pending', 'approved', 'rejected', 'paid'], array_column(WithdrawalStatus::cases(), 'value'));
        $this->assertSame(['pending', 'approved', 'rejected', 'released'], array_column(CapitalReleaseStatus::cases(), 'value'));
        $this->assertSame(['cash', 'profit'], array_column(WithdrawalSourceWalletType::cases(), 'value'));
    }

    public function test_foreign_keys_and_date_filters_are_indexed(): void
    {
        $expected = [
            'manual_deposits' => ['user_id', 'exchange_rate_id', 'reviewed_by', 'status', 'wallet_transaction_id', 'created_at'],
            'withdrawal_requests' => ['user_id', 'source_wallet_type', 'status', 'approved_by', 'wallet_transaction_id', 'requested_at'],
            'capital_release_requests' => ['user_id', 'status', 'approved_by', 'wallet_transaction_id', 'requested_at', 'release_at'],
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

    public function test_foreign_keys_use_the_designed_delete_rules(): void
    {
        $foreignKeys = collect(Schema::getForeignKeys('manual_deposits'))
            ->mapWithKeys(fn (array $key): array => [$key['columns'][0] => strtolower($key['on_delete'])]);

        $this->assertSame('restrict', $foreignKeys['user_id']);
        $this->assertSame('restrict', $foreignKeys['exchange_rate_id']);
        $this->assertSame('restrict', $foreignKeys['wallet_transaction_id']);
        $this->assertSame('set null', $foreignKeys['reviewed_by']);
    }

    public function test_capital_release_requests_carry_the_24h_release_date_column(): void
    {
        $this->assertTrue(Schema::hasColumn('capital_release_requests', 'release_at'));
        $this->assertTrue(Schema::hasColumn('capital_release_requests', 'released_at'));
    }
}
