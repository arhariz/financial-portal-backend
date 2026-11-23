<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PnlDistributionStatus;
use Database\Factories\PnlDistributionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PnlDistribution extends Model
{
    /** @use HasFactory<PnlDistributionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'symbol_id',
        'trade_date',
        'profit_loss',
        'entry_by',
        'status',
        'distributed_at',
        'reversal_of_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'profit_loss' => 'decimal:4',
            'status' => PnlDistributionStatus::class,
        ];
    }

    /** @return BelongsTo<Symbol, $this> */
    public function symbol(): BelongsTo
    {
        return $this->belongsTo(Symbol::class);
    }

    /** @return BelongsTo<User, $this> */
    public function entryBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entry_by');
    }

    /** @return BelongsTo<PnlDistribution, $this> */
    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of_id');
    }

    /** @return HasMany<PnlDistributionItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(PnlDistributionItem::class);
    }
}
