<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\KycStatus;
use App\Enums\PositionSource;
use App\Enums\PositionStatus;
use App\Enums\SymbolStatus;
use App\Enums\TransactionDirection;
use App\Enums\UserStatus;
use App\Enums\WalletTransactionType;
use App\Enums\WalletType;
use App\Models\AuditLog;
use App\Models\InternalTransfer;
use App\Models\Position;
use App\Models\Symbol;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\EligibilityDateService;
use App\Services\LedgerReconciliationService;
use App\Services\WalletService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InternalTransferTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function investor(): User
    {
        $investor = User::where('email', 'investor@forexbd.test')->firstOrFail();
        $investor->kyc_status = KycStatus::Approved;
        $investor->status = UserStatus::Active;
        $investor->save();

        return $investor;
    }

    private function activeSymbol(): Symbol
    {
        return Symbol::factory()->create(['status' => SymbolStatus::Active]);
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'from_wallet_type' => WalletType::Cash->value,
            'to_wallet_type' => WalletType::Investment->value,
            'amount_usd' => '100.0000',
        ], $overrides);
    }

    public function test_cash_to_investment_creates_a_fresh_position_and_two_ledger_rows(): void
    {
        $this->seed(DatabaseSeeder::class);
        Carbon::setTestNow(Carbon::parse('2026-11-23 06:00:00'));
        $investor = $this->investor();
        $symbol = $this->activeSymbol();
        app(WalletService::class)->deposit($investor, WalletType::Cash, '100.0000');

        $response = $this->actingAs($investor)->withHeader('Idempotency-Key', 'tr-001')
            ->postJson('/api/v1/transfers', $this->payload(['symbol_id' => $symbol->id]));

        $response->assertCreated()->assertJsonPath('data.to_wallet_type', WalletType::Investment->value);

        $transfer = InternalTransfer::firstOrFail();
        $this->assertSame('tr-001', $transfer->idempotency_key);
        $position = Position::firstOrFail();
        $this->assertSame($investor->id, $position->user_id);
        $this->assertSame($symbol->id, $position->symbol_id);
        $this->assertSame('100.0000', $position->capital);
        $this->assertSame('100.0000', $position->original_amount);
        $this->assertSame(PositionStatus::Active, $position->status);
        $this->assertSame(PositionSource::Cash, $position->source);
        $this->assertSame('2026-11-23', $position->investment_date->toDateString());
        $this->assertSame('2026-11-25', $position->eligible_date->toDateString());

        $investment = Wallet::where('user_id', $investor->id)->where('type', WalletType::Investment)->firstOrFail();
        $cash = Wallet::where('user_id', $investor->id)->where('type', WalletType::Cash)->firstOrFail();
        $this->assertSame('100.0000', $investment->balance);
        $this->assertSame('0.0000', $cash->balance);
        $rows = WalletTransaction::where('reference_type', InternalTransfer::class)->get();
        $this->assertCount(2, $rows);
        $this->assertSame(1, $rows->where('direction', TransactionDirection::Debit)->count());
        $this->assertSame(1, $rows->where('direction', TransactionDirection::Credit)->count());
        $this->assertTrue($rows->every(fn (WalletTransaction $row): bool => $row->type === WalletTransactionType::TransferIn || $row->type === WalletTransactionType::TransferOut));
        $this->assertSame(0, app(LedgerReconciliationService::class)->reconcile()['breakCount']);
        $this->assertSame(1, AuditLog::where('action', 'transfer.completed')->count());
    }

    public function test_profit_to_cash_is_allowed_without_creating_a_position(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        app(WalletService::class)->deposit($investor, WalletType::Profit, '50.0000');

        $this->actingAs($investor)->withHeader('Idempotency-Key', 'tr-profit-cash')
            ->postJson('/api/v1/transfers', $this->payload([
                'from_wallet_type' => WalletType::Profit->value,
                'to_wallet_type' => WalletType::Cash->value,
                'amount_usd' => '50.0000',
            ]))->assertCreated();

        $this->assertSame(0, Position::count());
        $this->assertSame('50.0000', Wallet::where('user_id', $investor->id)->where('type', WalletType::Cash)->firstOrFail()->balance);
        $this->assertSame('0.0000', Wallet::where('user_id', $investor->id)->where('type', WalletType::Profit)->firstOrFail()->balance);
    }

    public function test_profit_to_investment_creates_a_position_sourced_from_profit(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        $symbol = $this->activeSymbol();
        app(WalletService::class)->deposit($investor, WalletType::Profit, '40.0000');

        $this->actingAs($investor)->withHeader('Idempotency-Key', 'tr-profit-invest')
            ->postJson('/api/v1/transfers', $this->payload([
                'from_wallet_type' => WalletType::Profit->value,
                'amount_usd' => '40.0000',
                'symbol_id' => $symbol->id,
            ]))->assertCreated();

        $this->assertSame(PositionSource::Profit, Position::firstOrFail()->source);
        $this->assertSame('40.0000', Wallet::where('user_id', $investor->id)->where('type', WalletType::Investment)->firstOrFail()->balance);
    }

    public function test_commission_to_cash_is_allowed(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        app(WalletService::class)->deposit($investor, WalletType::Commission, '25.0000');

        $this->actingAs($investor)->withHeader('Idempotency-Key', 'tr-comm-cash')
            ->postJson('/api/v1/transfers', $this->payload([
                'from_wallet_type' => WalletType::Commission->value,
                'to_wallet_type' => WalletType::Cash->value,
                'amount_usd' => '25.0000',
            ]))->assertCreated();

        $this->assertSame('25.0000', Wallet::where('user_id', $investor->id)->where('type', WalletType::Cash)->firstOrFail()->balance);
        $this->assertSame(0, Position::count());
    }

    public function test_commission_to_investment_creates_a_position_sourced_from_commission(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        $symbol = $this->activeSymbol();
        app(WalletService::class)->deposit($investor, WalletType::Commission, '30.0000');

        $this->actingAs($investor)->withHeader('Idempotency-Key', 'tr-comm-invest')
            ->postJson('/api/v1/transfers', $this->payload([
                'from_wallet_type' => WalletType::Commission->value,
                'amount_usd' => '30.0000',
                'symbol_id' => $symbol->id,
            ]))->assertCreated();

        $this->assertSame(PositionSource::Commission, Position::firstOrFail()->source);
    }

    public function test_investment_to_cash_is_blocked_because_only_capital_release_can_do_it(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        app(WalletService::class)->deposit($investor, WalletType::Investment, '100.0000');

        $this->actingAs($investor)->withHeader('Idempotency-Key', 'tr-invest-cash')
            ->postJson('/api/v1/transfers', $this->payload([
                'from_wallet_type' => WalletType::Investment->value,
                'to_wallet_type' => WalletType::Cash->value,
            ]))->assertUnprocessable();

        $this->assertSame(0, InternalTransfer::count());
        $this->assertSame(0, WalletTransaction::where('reference_type', InternalTransfer::class)->count());
    }

    public function test_cash_to_profit_is_not_an_allowed_direction(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        app(WalletService::class)->deposit($investor, WalletType::Cash, '100.0000');

        $this->actingAs($investor)->withHeader('Idempotency-Key', 'tr-cash-profit')
            ->postJson('/api/v1/transfers', $this->payload([
                'to_wallet_type' => WalletType::Profit->value,
            ]))->assertUnprocessable();

        $this->assertSame(0, InternalTransfer::count());
    }

    public function test_suspended_user_cannot_transfer(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        $investor->status = UserStatus::Suspended;
        $investor->save();
        app(WalletService::class)->deposit($investor, WalletType::Cash, '100.0000');

        $this->actingAs($investor)->withHeader('Idempotency-Key', 'tr-suspended')
            ->postJson('/api/v1/transfers', $this->payload(['symbol_id' => $this->activeSymbol()->id]))
            ->assertForbidden();

        $this->assertSame(0, InternalTransfer::count());
    }

    public function test_user_without_approved_kyc_cannot_transfer(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = User::where('email', 'investor@forexbd.test')->firstOrFail();
        $investor->kyc_status = KycStatus::None;
        $investor->save();
        app(WalletService::class)->deposit($investor, WalletType::Cash, '100.0000');

        $this->actingAs($investor)->withHeader('Idempotency-Key', 'tr-no-kyc')
            ->postJson('/api/v1/transfers', $this->payload(['symbol_id' => $this->activeSymbol()->id]))
            ->assertForbidden();

        $this->assertSame(0, InternalTransfer::count());
    }

    public function test_duplicate_idempotency_key_returns_the_existing_transfer(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        $symbol = $this->activeSymbol();
        app(WalletService::class)->deposit($investor, WalletType::Cash, '100.0000');

        $first = $this->actingAs($investor)->withHeader('Idempotency-Key', 'same-key')
            ->postJson('/api/v1/transfers', $this->payload(['symbol_id' => $symbol->id]));
        $second = $this->actingAs($investor)->withHeader('Idempotency-Key', 'same-key')
            ->postJson('/api/v1/transfers', $this->payload(['symbol_id' => $symbol->id]));

        $first->assertCreated();
        $second->assertOk()->assertJsonPath('data.id', $first->json('data.id'));
        $this->assertSame(1, InternalTransfer::count());
        $this->assertSame(2, WalletTransaction::where('reference_type', InternalTransfer::class)->count());
    }

    public function test_investment_requires_an_existing_active_symbol(): void
    {
        $this->seed(DatabaseSeeder::class);
        $investor = $this->investor();
        app(WalletService::class)->deposit($investor, WalletType::Cash, '100.0000');

        $this->actingAs($investor)->withHeader('Idempotency-Key', 'tr-no-symbol')
            ->postJson('/api/v1/transfers', $this->payload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('symbol_id');

        $paused = Symbol::factory()->create(['status' => SymbolStatus::Paused]);
        $this->actingAs($investor)->withHeader('Idempotency-Key', 'tr-paused-symbol')
            ->postJson('/api/v1/transfers', $this->payload(['symbol_id' => $paused->id]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('symbol_id');

        $this->assertSame(0, InternalTransfer::count());
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function eligibilityFixtures(): array
    {
        return [
            ['2026-11-23', '2026-11-25'], // Monday → Wednesday
            ['2026-11-24', '2026-11-26'], // Tuesday → Thursday
            ['2026-11-25', '2026-12-01'], // Wednesday → Friday is weekend → Tuesday
            ['2026-11-26', '2026-12-01'], // Thursday → Tuesday
            ['2026-11-27', '2026-12-01'], // Friday → Tuesday
            ['2026-11-28', '2026-12-01'], // Saturday → Tuesday
            ['2026-11-29', '2026-12-01'], // Sunday → Tuesday
        ];
    }

    #[DataProvider('eligibilityFixtures')]
    public function test_t_plus_two_eligibility_follows_the_asia_dhaka_weekend_rule(string $investmentDate, string $expected): void
    {
        $calculator = app(EligibilityDateService::class);

        $this->assertSame($expected, $calculator->forDate($investmentDate)->toDateString());
    }

    public function test_unauthenticated_transfer_is_rejected(): void
    {
        $this->postJson('/api/v1/transfers', $this->payload())->assertUnauthorized();
    }
}
