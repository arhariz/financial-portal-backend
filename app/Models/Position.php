<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PositionSource;
use App\Enums\PositionStatus;
use Database\Factories\PositionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $capital
 * @property string $original_amount
 * @property Carbon $investment_date
 * @property Carbon $eligible_date
 * @property PositionStatus $status
 * @property PositionSource $source
 */
class Position extends Model
{
    /** @use HasFactory<PositionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'symbol_id',
        'capital',
        'original_amount',
        'investment_date',
        'eligible_date',
        'status',
        'source',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capital' => 'decimal:4',
            'original_amount' => 'decimal:4',
            'investment_date' => 'date',
            'eligible_date' => 'date',
            'status' => PositionStatus::class,
            'source' => PositionSource::class,
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
}
