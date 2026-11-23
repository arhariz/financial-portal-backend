<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CapitalReleaseStatus;
use App\Models\CapitalReleaseRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CapitalReleaseRequest>
 */
class CapitalReleaseRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount_usd' => '100.0000',
            'status' => CapitalReleaseStatus::Pending,
            'approved_by' => null,
            'approved_at' => null,
            'release_at' => now()->addDay(),
            'released_at' => null,
            'wallet_transaction_id' => null,
        ];
    }
}
