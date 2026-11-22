<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Development-only password for every seeded account. Never used in production.
     */
    private const PASSWORD = 'Password@123';

    /**
     * @return array<int, array{email: string, name: string, role: string}>
     */
    private static function accounts(): array
    {
        return [
            ['email' => 'admin@forexbd.test', 'name' => 'Platform Owner', 'role' => 'super_admin'],
            ['email' => 'admin2@forexbd.test', 'name' => 'Administrator', 'role' => 'admin'],
            ['email' => 'kyc@forexbd.test', 'name' => 'KYC Reviewer', 'role' => 'kyc_reviewer'],
            ['email' => 'pnl@forexbd.test', 'name' => 'P&L Operator', 'role' => 'pnl_operator'],
            ['email' => 'finance@forexbd.test', 'name' => 'Finance Manager', 'role' => 'finance_manager'],
            ['email' => 'credit@forexbd.test', 'name' => 'Credit Manager', 'role' => 'credit_manager'],
            ['email' => 'commission@forexbd.test', 'name' => 'Commission Manager', 'role' => 'commission_manager'],
            ['email' => 'support@forexbd.test', 'name' => 'Support Agent', 'role' => 'support'],
            ['email' => 'agent1@forexbd.test', 'name' => 'Seed Investor', 'role' => 'agent_tier_1'],
            ['email' => 'agent2@forexbd.test', 'name' => 'Growth Investor', 'role' => 'agent_tier_2'],
            ['email' => 'agent3@forexbd.test', 'name' => 'Master Investor', 'role' => 'agent_tier_3'],
            ['email' => 'investor@forexbd.test', 'name' => 'Demo Investor', 'role' => 'investor'],
            ['email' => 'investor2@forexbd.test', 'name' => 'Demo Investor Two', 'role' => 'investor'],
        ];
    }

    public function run(): void
    {
        foreach (self::accounts() as $account) {
            $user = User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => self::PASSWORD,
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles([$account['role']]);
        }
    }
}
