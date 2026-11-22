<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\KycStatus;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IdentitySchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_has_all_identity_columns(): void
    {
        $expected = [
            'id',
            'name',
            'email',
            'phone',
            'password',
            'status',
            'email_verified_at',
            'phone_verified_at',
            'kyc_status',
            'referral_code_used',
            'two_factor_secret',
            'created_by',
            'remember_token',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        foreach ($expected as $column) {
            $this->assertTrue(Schema::hasColumn('users', $column), "Missing users.{$column}");
        }
    }

    public function test_user_profiles_table_has_all_columns(): void
    {
        $expected = [
            'id',
            'user_id',
            'full_name',
            'date_of_birth',
            'address',
            'city',
            'country',
            'id_number',
            'created_at',
            'updated_at',
        ];

        foreach ($expected as $column) {
            $this->assertTrue(Schema::hasColumn('user_profiles', $column), "Missing user_profiles.{$column}");
        }
    }

    public function test_spatie_role_tables_exist(): void
    {
        foreach (['roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }
    }

    public function test_status_enums_match_schema_registry(): void
    {
        $this->assertSame(['active', 'suspended'], array_column(UserStatus::cases(), 'value'));
        $this->assertSame(['none', 'pending', 'approved', 'rejected'], array_column(KycStatus::cases(), 'value'));
    }

    public function test_identity_columns_are_indexed(): void
    {
        $userIndexes = collect(Schema::getIndexes('users'))->pluck('columns')->flatten()->all();
        $this->assertContains('status', $userIndexes);
        $this->assertContains('kyc_status', $userIndexes);

        $profileIndexes = collect(Schema::getIndexes('user_profiles'))->pluck('columns')->flatten()->all();
        $this->assertContains('user_id', $profileIndexes);
    }

    public function test_user_model_casts_identity_status_enums(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(UserStatus::class, $user->status);
        $this->assertInstanceOf(KycStatus::class, $user->kyc_status);
        $this->assertSame('active', $user->status->value);
        $this->assertSame('none', $user->kyc_status->value);
    }
}
