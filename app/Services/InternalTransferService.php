<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PositionSource;
use App\Enums\PositionStatus;
use App\Enums\SymbolStatus;
use App\Enums\WalletTransactionType;
use App\Enums\WalletType;
use App\Models\InternalTransfer;
use App\Models\Position;
use App\Models\Symbol;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/**
 * User-initiated internal wallet transfers (D-005/D-008, WAL-02..05).
 * Investment→Cash is deliberately excluded: it only happens through an
 * approved capital release after 24h (WAL-06).
 */
final class InternalTransferService
{
    /**
     * Locked direction → resulting position source (null = no position).
     *
     * @var array<string, PositionSource|null>
     */
    private const DIRECTIONS = [
        'cash:investment' => PositionSource::Cash,
        'profit:cash' => null,
        'profit:investment' => PositionSource::Profit,
        'commission:cash' => null,
        'commission:investment' => PositionSource::Commission,
    ];

    public function __construct(
        private readonly WalletService $wallets,
        private readonly EligibilityDateService $eligibility,
        private readonly AuditService $audit,
        private readonly GuardService $guard,
    ) {
    }

    /**
     * @param array{from: WalletType, to: WalletType, amount_usd: string, symbol_id: ?int} $data
     *
     * @return array{transfer: InternalTransfer, created: bool}
     */
    public function transfer(User $user, array $data, ?string $idempotencyKey): array
    {
        $this->guard->ensureCanTransfer($user);

        if ($idempotencyKey !== null) {
            $existing = $this->findByIdempotencyKey($user, $idempotencyKey);

            if ($existing !== null) {
                return ['transfer' => $existing, 'created' => false];
            }
        }

        $direction = $data['from']->value.':'.$data['to']->value;

        if (! array_key_exists($direction, self::DIRECTIONS)) {
            throw new InvalidArgumentException('This wallet transfer direction is not allowed.');
        }

        if (bccomp($data['amount_usd'], '0.0000', 4) <= 0) {
            throw new InvalidArgumentException('The transfer amount must be greater than zero.');
        }

        $source = self::DIRECTIONS[$direction];

        if ($data['to'] === WalletType::Investment) {
            $this->assertInvestableSymbol($data['symbol_id']);
        }

        /** @var array{transfer: InternalTransfer, created: bool} $result */
        $result = DB::transaction(fn (): array => $this->postTransfer($user, $data, $source, $idempotencyKey));

        return $result;
    }

    private function assertInvestableSymbol(?int $symbolId): void
    {
        if ($symbolId === null) {
            throw ValidationException::withMessages(['symbol_id' => ['An active symbol is required when investing.']]);
        }

        $symbol = Symbol::find($symbolId);

        if ($symbol === null || $symbol->status !== SymbolStatus::Active) {
            throw ValidationException::withMessages(['symbol_id' => ['The selected symbol is not active.']]);
        }
    }

    /**
     * @param array{from: WalletType, to: WalletType, amount_usd: string, symbol_id: ?int} $data
     *
     * @return array{transfer: InternalTransfer, created: bool}
     */
    private function postTransfer(User $user, array $data, ?PositionSource $source, ?string $idempotencyKey): array
    {
        try {
            $transfer = InternalTransfer::create([
                'user_id' => $user->id,
                'from_wallet_type' => $data['from'],
                'to_wallet_type' => $data['to'],
                'amount_usd' => $data['amount_usd'],
                'symbol_id' => $data['symbol_id'],
                'idempotency_key' => $idempotencyKey,
            ]);
        } catch (UniqueConstraintViolationException) {
            /** @var InternalTransfer $existing */
            $existing = $this->findByIdempotencyKey($user, (string) $idempotencyKey);

            return ['transfer' => $existing, 'created' => false];
        }

        $this->wallets->transfer(
            $user,
            $data['from'],
            $data['to'],
            $data['amount_usd'],
            WalletTransactionType::TransferOut,
            InternalTransfer::class,
            $transfer->id,
            $user,
            WalletTransactionType::TransferOut,
            WalletTransactionType::TransferIn,
        );

        if ($data['to'] === WalletType::Investment && $source !== null) {
            $investmentDate = $this->eligibility->investmentDate();
            $position = Position::create([
                'user_id' => $user->id,
                'symbol_id' => $data['symbol_id'],
                'capital' => $data['amount_usd'],
                'original_amount' => $data['amount_usd'],
                'investment_date' => $investmentDate->toDateString(),
                'eligible_date' => $this->eligibility->forDate($investmentDate->toDateString())->toDateString(),
                'status' => PositionStatus::Active,
                'source' => $source,
            ]);
            $transfer->position_id = $position->id;
            $transfer->save();
        }

        $this->audit->log($user, 'transfer.completed', InternalTransfer::class, $transfer->id, [
            'from' => $data['from']->value,
            'to' => $data['to']->value,
            'amount_usd' => $data['amount_usd'],
        ]);

        return ['transfer' => $transfer, 'created' => true];
    }

    private function findByIdempotencyKey(User $user, string $key): ?InternalTransfer
    {
        return InternalTransfer::query()
            ->where('user_id', $user->id)
            ->where('idempotency_key', $key)
            ->first();
    }
}
