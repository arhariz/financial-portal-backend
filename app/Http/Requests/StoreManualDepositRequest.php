<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreManualDepositRequest extends FormRequest
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
            'amount_bdt' => ['required', 'string', 'regex:/^\d{1,14}\.\d{4}$/', 'gt:0'],
            'payment_method' => ['required', 'string', 'max:50'],
            'proof_path' => ['required', 'string', 'max:512', 'regex:/^deposits\/\d+\/[A-Za-z0-9_-]+$/'],
        ];
    }
}
