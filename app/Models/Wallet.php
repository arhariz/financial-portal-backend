<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WalletType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property WalletType $type
 */
class Wallet extends Model
{
    /** @use HasFactory<\Database\Factories\WalletFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'currency',
        'balance',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => WalletType::class,
            'balance' => 'decimal:4',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<WalletTransaction, $this> */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
