<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWithdrawalRequest extends FormRequest
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
            'source_wallet_type' => ['required', 'string', 'in:cash,profit'],
            'amount_usd' => ['required', 'string', 'regex:/^\d{1,14}\.\d{4}$/', 'gt:0'],
            'payout_method' => ['required', 'string', 'in:bank,crypto'],
            'payout_details' => ['required', 'array'],
            'payout_details.account' => ['required', 'string', 'max:255'],
        ];
    }
}
