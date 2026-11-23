<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WithdrawalSourceWalletType;
use App\Enums\WithdrawalStatus;
use Database\Factories\WithdrawalRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property WithdrawalStatus $status
 * @property WithdrawalSourceWalletType $source_wallet_type
 * @property string $amount_usd
 * @property Carbon|null $requested_at
 * @property Carbon|null $approved_at
 * @property Carbon|null $paid_at
 */
class WithdrawalRequest extends Model
{
    /** @use HasFactory<WithdrawalRequestFactory> */
    use HasFactory;

    public const CREATED_AT = 'requested_at';

    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'source_wallet_type',
        'amount_usd',
        'payout_method',
        'payout_details',
        'idempotency_key',
        'status',
        'approved_by',
        'approved_at',
        'paid_at',
        'rejection_reason',
        'wallet_transaction_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source_wallet_type' => WithdrawalSourceWalletType::class,
            'amount_usd' => 'decimal:4',
            'payout_details' => 'array',
            'status' => WithdrawalStatus::class,
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return BelongsTo<WalletTransaction, $this> */
    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class);
    }
}
