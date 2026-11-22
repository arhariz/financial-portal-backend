<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionDirection;
use App\Enums\WalletTransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class WalletTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\WalletTransactionFactory> */
    use HasFactory;

    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'wallet_id',
        'direction',
        'amount',
        'balance_after',
        'type',
        'reference_type',
        'reference_id',
        'counterparty_wallet_id',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'direction' => TransactionDirection::class,
            'type' => WalletTransactionType::class,
            'amount' => 'decimal:4',
            'balance_after' => 'decimal:4',
        ];
    }

    protected function performUpdate(Builder $query): bool
    {
        throw new LogicException('Ledger entries are immutable; use a reversal entry.');
    }

    protected function performDeleteOnModel(): void
    {
        throw new LogicException('Ledger entries are immutable; use a reversal entry.');
    }

    /** @return BelongsTo<Wallet, $this> */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /** @return BelongsTo<Wallet, $this> */
    public function counterparty(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'counterparty_wallet_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
