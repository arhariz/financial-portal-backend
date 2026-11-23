<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\CommissionEvent;
use App\Enums\CommissionStatus;
use App\Enums\KycStatus;
use App\Enums\PnlDistributionStatus;
use App\Enums\UserStatus;
use App\Enums\WalletType;
use App\Models\AuditLog;
use App\Models\Commission;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Tests\TestCase;

class SchemaGuardTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    private const BUSINESS_TABLES = [
        'users', 'user_profiles', 'kyc_document_types', 'kyc_documents',
        'wallets', 'wallet_transactions', 'exchange_rates',
        'manual_deposits', 'withdrawal_requests', 'capital_release_requests',
        'symbols', 'positions', 'pnl_distributions', 'pnl_distribution_items',
        'agents', 'referrals', 'deduction_profiles', 'commission_rules', 'commissions',
        'notification_templates', 'notifications', 'notification_preferences',
        'dashboard_layouts', 'audit_logs', 'login_history',
    ];

    public function test_every_registered_business_table_exists(): void
    {
        foreach (self::BUSINESS_TABLES as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table {$table}");
        }
    }

    public function test_no_float_columns_exist_in_business_tables(): void
    {
        foreach (self::BUSINESS_TABLES as $table) {
            foreach (Schema::getColumnListing($table) as $column) {
                $this->assertNotContains(
                    Schema::getColumnType($table, $column),
                    ['float', 'double', 'real'],
                    "Float column: {$table}.{$column}"
                );
            }
        }
    }

    public function test_money_columns_are_decimal_and_cast_as_decimal_strings(): void
    {
        $columns = [
            ['wallets', 'balance'],
            ['wallet_transactions', 'amount'],
            ['wallet_transactions', 'balance_after'],
            ['exchange_rates', 'bdt_per_usd'],
            ['positions', 'capital'],
            ['pnl_distributions', 'profit_loss'],
            ['commission_rules', 'rate'],
            ['commissions', 'base_amount'],
            ['commissions', 'amount'],
        ];

        foreach ($columns as [$table, $column]) {
            $this->assertContains(Schema::getColumnType($table, $column), ['decimal', 'numeric']);
        }

        $this->assertStringContainsString('decimal', (new \App\Models\Wallet())->getCasts()['balance']);
        $this->assertStringContainsString('decimal', (new Commission())->getCasts()['amount']);
        $this->assertStringContainsString('decimal', (new \App\Models\CommissionRule())->getCasts()['rate']);
    }

    public function test_all_foreign_keys_are_indexed(): void
    {
        $foreignKeys = [
            'user_profiles' => ['user_id'],
            'kyc_documents' => ['user_id', 'type_id', 'reviewed_by'],
            'wallets' => ['user_id'],
            'wallet_transactions' => ['wallet_id', 'counterparty_wallet_id', 'created_by'],
            'exchange_rates' => ['set_by'],
            'manual_deposits' => ['user_id', 'exchange_rate_id', 'wallet_transaction_id', 'reviewed_by'],
            'withdrawal_requests' => ['user_id', 'wallet_transaction_id'],
            'capital_release_requests' => ['user_id', 'wallet_transaction_id'],
            'positions' => ['user_id', 'symbol_id'],
            'pnl_distributions' => ['symbol_id', 'entry_by', 'reversal_of_id'],
            'pnl_distribution_items' => ['distribution_id', 'position_id', 'user_id', 'wallet_transaction_id'],
            'agents' => ['user_id', 'upline_agent_id'],
            'referrals' => ['agent_id', 'referred_user_id'],
            'deduction_profiles' => ['created_by'],
            'commission_rules' => ['deduction_profile_id'],
            'commissions' => ['agent_id', 'rule_id', 'source_user_id', 'wallet_transaction_id'],
            'notifications' => ['user_id'],
            'notification_preferences' => ['user_id'],
            'dashboard_layouts' => ['role_id', 'user_id', 'updated_by'],
            'audit_logs' => ['actor_id'],
            'login_history' => ['user_id'],
        ];

        foreach ($foreignKeys as $table => $columns) {
            $indexed = collect(Schema::getIndexes($table))
                ->flatMap(fn (array $index): array => $index['columns'])
                ->all();

            foreach ($columns as $column) {
                $this->assertContains($column, $indexed, "Missing FK index on {$table}.{$column}");
            }
        }
    }

    public function test_status_columns_use_php_backed_enums(): void
    {
        $expected = [
            [\App\Models\User::class, 'status', UserStatus::class],
            [\App\Models\User::class, 'kyc_status', KycStatus::class],
            [\App\Models\Wallet::class, 'type', WalletType::class],
            [\App\Models\PnlDistribution::class, 'status', PnlDistributionStatus::class],
            [Commission::class, 'event', CommissionEvent::class],
            [Commission::class, 'status', CommissionStatus::class],
        ];

        foreach ($expected as [$modelClass, $attribute, $enumClass]) {
            $casts = (new $modelClass())->getCasts();
            $this->assertSame($enumClass, $casts[$attribute], "Missing enum cast for {$attribute}");
        }
    }

    public function test_append_only_models_reject_mutation(): void
    {
        $models = [
            WalletTransaction::factory()->create(),
            Commission::factory()->create(),
            AuditLog::factory()->create(),
        ];

        foreach ($models as $model) {
            $rejected = false;

            try {
                $model->delete();
            } catch (LogicException) {
                $rejected = true;
            }

            $this->assertTrue($rejected, 'Append-only model accepted a delete.');
        }
    }
}
