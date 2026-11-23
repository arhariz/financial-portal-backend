<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\CapitalReleaseRequest
 */
class CapitalReleaseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount_usd' => $this->amount_usd,
            'status' => $this->status->value,
            'requested_at' => $this->requested_at?->toIso8601String(),
            'release_at' => $this->release_at?->toIso8601String(),
        ];
    }
}
