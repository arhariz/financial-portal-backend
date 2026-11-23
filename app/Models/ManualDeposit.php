<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ManualDepositStatus;
use Database\Factories\ManualDepositFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property ManualDepositStatus $status
 * @property Carbon|null $reviewed_at
 * @property string $amount_usd
 * @property string $amount_bdt
 * @property string $proof_path
 */
class ManualDeposit extends Model
{
    /** @use HasFactory<ManualDepositFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'amount_usd',
        'amount_bdt',
        'exchange_rate_id',
        'payment_method',
        'proof_path',
        'idempotency_key',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'wallet_transaction_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_usd' => 'decimal:4',
            'amount_bdt' => 'decimal:4',
            'status' => ManualDepositStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<ExchangeRate, $this> */
    public function exchangeRate(): BelongsTo
    {
        return $this->belongsTo(ExchangeRate::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** @return BelongsTo<WalletTransaction, $this> */
    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class);
    }
}
