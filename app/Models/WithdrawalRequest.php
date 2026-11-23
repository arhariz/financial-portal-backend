<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WithdrawalSourceWalletType;
use App\Enums\WithdrawalStatus;
use Database\Factories\WithdrawalRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
