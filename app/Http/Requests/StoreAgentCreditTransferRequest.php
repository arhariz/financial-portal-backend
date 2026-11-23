<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgentCreditTransferRequest extends FormRequest
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
            'investor_id' => ['required', 'integer', 'exists:users,id'],
            'amount_usd' => ['required', 'string', 'regex:/^\d{1,14}\.\d{4}$/', 'gt:0'],
        ];
    }
}
