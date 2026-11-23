<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AgentCreditPurchaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $amount_bdt
 * @property string $amount_usd
 * @property string $bonus_amount_usd
 */
class AgentCreditPurchase extends Model
{
    /** @use HasFactory<AgentCreditPurchaseFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'amount_bdt',
        'amount_usd',
        'bonus_amount_usd',
        'bonus_rule_id',
        'agent_tier',
        'exchange_rate_id',
        'idempotency_key',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_bdt' => 'decimal:4',
            'amount_usd' => 'decimal:4',
            'bonus_amount_usd' => 'decimal:4',
            'agent_tier' => 'integer',
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
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
