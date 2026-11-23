<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SymbolCategory;
use App\Enums\SymbolStatus;
use Database\Factories\SymbolFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Symbol extends Model
{
    /** @use HasFactory<SymbolFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'category',
        'status',
        'min_investment',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => SymbolCategory::class,
            'status' => SymbolStatus::class,
            'min_investment' => 'decimal:4',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
