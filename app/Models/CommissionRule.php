<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CommissionEvent;
use Database\Factories\CommissionRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionRule extends Model
{
    /** @use HasFactory<CommissionRuleFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event',
        'agent_tier',
        'level',
        'rate',
        'enabled',
        'holding_period_hours',
        'deduction_profile_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event' => CommissionEvent::class,
            'agent_tier' => 'integer',
            'level' => 'integer',
            'rate' => 'decimal:6',
            'enabled' => 'boolean',
            'holding_period_hours' => 'integer',
        ];
    }

    /** @return BelongsTo<DeductionProfile, $this> */
    public function deductionProfile(): BelongsTo
    {
        return $this->belongsTo(DeductionProfile::class);
    }

    /** @return HasMany<Commission, $this> */
    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'rule_id');
    }
}
