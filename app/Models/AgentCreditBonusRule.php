<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AgentCreditBonusRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $rate
 */
class AgentCreditBonusRule extends Model
{
    /** @use HasFactory<AgentCreditBonusRuleFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tier',
        'rate',
        'active',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tier' => 'integer',
            'rate' => 'decimal:6',
            'active' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
