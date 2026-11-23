<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\AgentCreditTransfer
 */
class AgentCreditTransferResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agent_id' => $this->agent_id,
            'investor_id' => $this->investor_id,
            'amount_usd' => $this->amount_usd,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
