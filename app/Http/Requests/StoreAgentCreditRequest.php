<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgentCreditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'agent_id' => ['required', 'integer', 'exists:users,id'],
            'amount_bdt' => ['required', 'string', 'regex:/^\d{1,14}\.\d{4}$/', 'gt:0'],
            'bonus_amount_usd' => ['required', 'string', 'regex:/^\d{1,14}\.\d{4}$/'],
        ];
    }
}
