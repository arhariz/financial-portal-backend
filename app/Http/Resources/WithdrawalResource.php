<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\WithdrawalRequest
 */
class WithdrawalResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'source_wallet_type' => $this->source_wallet_type->value,
            'amount_usd' => $this->amount_usd,
            'payout_method' => $this->payout_method,
            'status' => $this->status->value,
            'requested_at' => $this->requested_at?->toIso8601String(),
        ];
    }
}
