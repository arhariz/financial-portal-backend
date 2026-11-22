<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_rbac_and_audit_tables_exist_after_fresh_migration(): void
    {
        foreach (['roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions', 'activity_log'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }
    }

    public function test_activity_log_table_has_expected_columns(): void
    {
        foreach (['log_name', 'description', 'subject_type', 'subject_id', 'causer_type', 'causer_id', 'properties', 'event', 'batch_uuid'] as $column) {
            $this->assertTrue(Schema::hasColumn('activity_log', $column), "Missing column: {$column}");
        }
    }
}
