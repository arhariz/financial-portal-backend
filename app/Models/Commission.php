<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CommissionEvent;
use App\Enums\CommissionStatus;
use Database\Factories\CommissionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class Commission extends Model
{
    /** @use HasFactory<CommissionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'agent_id',
        'rule_id',
        'event',
        'source_user_id',
        'source_reference_type',
        'source_reference_id',
        'base_amount',
        'amount',
        'status',
        'vest_at',
        'wallet_transaction_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event' => CommissionEvent::class,
            'status' => CommissionStatus::class,
            'base_amount' => 'decimal:4',
            'amount' => 'decimal:4',
        ];
    }

    protected function performUpdate(Builder $query): bool
    {
        throw new LogicException('Commission rows are immutable; use a reversal entry.');
    }

    protected function performDeleteOnModel(): void
    {
        throw new LogicException('Commission rows are immutable; use a reversal entry.');
    }

    /** @return BelongsTo<Agent, $this> */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /** @return BelongsTo<CommissionRule, $this> */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(CommissionRule::class, 'rule_id');
    }

    /** @return BelongsTo<User, $this> */
    public function sourceUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'source_user_id');
    }

    /** @return BelongsTo<WalletTransaction, $this> */
    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'wallet_transaction_id');
    }
}
