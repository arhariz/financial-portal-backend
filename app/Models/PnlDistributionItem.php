<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PnlDistributionItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PnlDistributionItem extends Model
{
    /** @use HasFactory<PnlDistributionItemFactory> */
    use HasFactory;

    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'distribution_id',
        'position_id',
        'user_id',
        'share_ratio',
        'amount',
        'wallet_transaction_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'share_ratio' => 'decimal:6',
            'amount' => 'decimal:4',
        ];
    }

    /** @return BelongsTo<PnlDistribution, $this> */
    public function distribution(): BelongsTo
    {
        return $this->belongsTo(PnlDistribution::class);
    }

    /** @return BelongsTo<Position, $this> */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<WalletTransaction, $this> */
    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class);
    }
}
