<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CapitalReleaseStatus;
use Database\Factories\CapitalReleaseRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property CapitalReleaseStatus $status
 * @property string $amount_usd
 * @property Carbon|null $requested_at
 * @property Carbon|null $approved_at
 * @property Carbon|null $release_at
 * @property Carbon|null $released_at
 */
class CapitalReleaseRequest extends Model
{
    /** @use HasFactory<CapitalReleaseRequestFactory> */
    use HasFactory;

    public const CREATED_AT = 'requested_at';

    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'amount_usd',
        'idempotency_key',
        'status',
        'approved_by',
        'approved_at',
        'release_at',
        'released_at',
        'wallet_transaction_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_usd' => 'decimal:4',
            'status' => CapitalReleaseStatus::class,
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'release_at' => 'datetime',
            'released_at' => 'datetime',
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
