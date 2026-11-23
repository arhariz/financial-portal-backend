<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\AgentCreditPurchase
 */
class AgentCreditPurchaseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agent_id' => $this->user_id,
            'amount_bdt' => $this->amount_bdt,
            'amount_usd' => $this->amount_usd,
            'bonus_amount_usd' => $this->bonus_amount_usd,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
