<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AgentCreditTransferFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $amount_usd
 */
class AgentCreditTransfer extends Model
{
    /** @use HasFactory<AgentCreditTransferFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'agent_id',
        'investor_id',
        'amount_usd',
        'idempotency_key',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_usd' => 'decimal:4',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /** @return BelongsTo<User, $this> */
    public function investor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investor_id');
    }
}
