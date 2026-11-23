<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
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
            'from_wallet_type' => ['required', 'string', 'in:cash,profit,commission,investment'],
            'to_wallet_type' => ['required', 'string', 'in:cash,profit,commission,investment'],
            'amount_usd' => ['required', 'string', 'regex:/^\d{1,14}\.\d{4}$/', 'gt:0'],
            'symbol_id' => ['nullable', 'integer', 'exists:symbols,id'],
        ];
    }
}
