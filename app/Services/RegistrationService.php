<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\KycStatus;
use App\Enums\UserStatus;
use App\Models\Agent;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class RegistrationService
{
    /**
     * @param array<string, mixed> $data
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => UserStatus::Active,
                'kyc_status' => KycStatus::None,
                'referral_code_used' => $data['referral_code'] ?? null,
            ]);

            $user->assignRole('investor');

            if (! empty($data['referral_code'])) {
                $agent = Agent::where('referral_code', $data['referral_code'])->firstOrFail();

                Referral::create([
                    'agent_id' => $agent->id,
                    'referred_user_id' => $user->id,
                    'level' => 1,
                ]);
            }

            return $user;
        });
    }
}
