<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WalletType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property WalletType $from_wallet_type
 * @property WalletType $to_wallet_type
 * @property string $amount_usd
 */
class InternalTransfer extends Model
{
    /** @use HasFactory<\Database\Factories\InternalTransferFactory> */
    use HasFactory;

    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'from_wallet_type',
        'to_wallet_type',
        'amount_usd',
        'symbol_id',
        'position_id',
        'idempotency_key',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'from_wallet_type' => WalletType::class,
            'to_wallet_type' => WalletType::class,
            'amount_usd' => 'decimal:4',
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Symbol, $this> */
    public function symbol(): BelongsTo
    {
        return $this->belongsTo(Symbol::class);
    }

    /** @return BelongsTo<Position, $this> */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
