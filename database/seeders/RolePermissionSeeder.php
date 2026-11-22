<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Permissions\Manifest;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        Manifest::sync();
    }
}
