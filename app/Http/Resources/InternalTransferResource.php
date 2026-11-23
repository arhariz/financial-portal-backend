<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\InternalTransfer
 */
class InternalTransferResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'from_wallet_type' => $this->from_wallet_type->value,
            'to_wallet_type' => $this->to_wallet_type->value,
            'amount_usd' => $this->amount_usd,
            'symbol_id' => $this->symbol_id,
            'position_id' => $this->position_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
