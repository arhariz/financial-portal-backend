<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WalletType;
use App\Models\InternalTransfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InternalTransfer>
 */
class InternalTransferFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'from_wallet_type' => WalletType::Cash,
            'to_wallet_type' => WalletType::Investment,
            'amount_usd' => '100.0000',
            'symbol_id' => null,
            'position_id' => null,
            'idempotency_key' => null,
        ];
    }
}
