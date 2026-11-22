<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_user_resource(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs(User::where('email', 'admin@forexbd.test')->firstOrFail())
            ->get('/admin/users')
            ->assertOk();
    }

    public function test_non_admin_cannot_view_user_resource(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->actingAs(User::where('email', 'support@forexbd.test')->firstOrFail())
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_super_admin_can_manage_users(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@forexbd.test')->firstOrFail();

        $this->assertTrue($admin->can('users.manage'));
        $this->assertTrue($admin->can('viewAny', User::class));
    }

    public function test_non_admin_cannot_manage_users(): void
    {
        $this->seed(DatabaseSeeder::class);

        $support = User::where('email', 'support@forexbd.test')->firstOrFail();

        $this->assertFalse($support->can('users.manage'));
        $this->assertFalse($support->can('viewAny', User::class));
    }
}
