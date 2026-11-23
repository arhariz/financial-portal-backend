<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\NotificationChannel;
use App\Enums\NotificationEvent;
use App\Enums\NotificationStatus;
use App\Models\AuditLog;
use App\Models\DashboardLayout;
use App\Models\LoginHistory;
use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Tests\TestCase;

class NotificationAuditSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_audit_tables_have_expected_columns(): void
    {
        $expected = [
            'notification_templates' => ['id', 'key', 'channel', 'subject', 'body', 'variables', 'enabled'],
            'notifications' => ['id', 'user_id', 'template_key', 'channel', 'payload', 'status', 'sent_at', 'created_at'],
            'notification_preferences' => ['id', 'user_id', 'channel', 'event', 'enabled'],
            'dashboard_layouts' => ['id', 'role_id', 'user_id', 'widgets', 'updated_by', 'created_at', 'updated_at'],
            'audit_logs' => ['id', 'actor_id', 'actor_role', 'action', 'subject_type', 'subject_id', 'ip', 'user_agent', 'changes', 'created_at'],
            'login_history' => ['id', 'user_id', 'ip', 'user_agent', 'device', 'logged_in_at', 'logged_out_at'],
        ];

        foreach ($expected as $table => $columns) {
            foreach ($columns as $column) {
                $this->assertTrue(Schema::hasColumn($table, $column), "Missing {$table}.{$column}");
            }
        }

        $this->assertFalse(Schema::hasColumn('notifications', 'updated_at'));
        $this->assertFalse(Schema::hasColumn('audit_logs', 'updated_at'));
    }

    public function test_channels_statuses_and_events_match_locked_groups(): void
    {
        $this->assertSame(
            ['email', 'sms', 'in_app', 'push'],
            array_column(NotificationChannel::cases(), 'value')
        );
        $this->assertSame(
            ['queued', 'sent', 'failed'],
            array_column(NotificationStatus::cases(), 'value')
        );
        $this->assertSame(
            ['kyc', 'deposits_withdrawals', 'pnl_distribution', 'commissions_agent', 'security_alert'],
            array_column(NotificationEvent::cases(), 'value')
        );
    }

    public function test_security_alert_preference_cannot_be_disabled(): void
    {
        /** @var array<string, array{label: string, mandatory: bool}> $events */
        $events = config('notification_events.events');

        $this->assertSame(
            array_column(NotificationEvent::cases(), 'value'),
            array_keys($events)
        );
        $this->assertTrue($events['security_alert']['mandatory']);
        $this->assertFalse($events['kyc']['mandatory']);
        $this->assertTrue(NotificationEvent::SecurityAlert->isMandatory());
    }

    public function test_foreign_keys_statuses_and_date_filters_are_indexed(): void
    {
        $expected = [
            'notifications' => ['user_id', 'template_key', 'channel', 'status', 'sent_at', 'created_at'],
            'notification_preferences' => ['user_id', 'channel', 'event'],
            'dashboard_layouts' => ['role_id', 'user_id', 'updated_by', 'created_at'],
            'audit_logs' => ['actor_id', 'actor_role', 'action', 'subject_type', 'subject_id', 'created_at'],
            'login_history' => ['user_id', 'logged_in_at', 'logged_out_at'],
        ];

        foreach ($expected as $table => $columns) {
            $indexed = collect(Schema::getIndexes($table))
                ->flatMap(fn (array $index): array => $index['columns'])
                ->all();

            foreach ($columns as $column) {
                $this->assertContains($column, $indexed, "Missing index on {$table}.{$column}");
            }
        }
    }

    public function test_json_payloads_cast_to_arrays_and_have_no_float_columns(): void
    {
        $template = NotificationTemplate::factory()->create(['variables' => ['amount' => '100.0000']]);
        $notification = Notification::factory()->create(['payload' => ['amount' => '100.0000']]);
        $layout = DashboardLayout::factory()->create(['widgets' => ['health' => true]]);
        $log = AuditLog::factory()->create(['changes' => ['before' => '0.0000']]);

        $this->assertSame(['amount' => '100.0000'], $template->fresh()->variables);
        $this->assertSame(['amount' => '100.0000'], $notification->fresh()->payload);
        $this->assertSame(['health' => true], $layout->fresh()->widgets);
        $this->assertSame(['before' => '0.0000'], $log->fresh()->changes);

        foreach (['notification_templates' => 'variables', 'notifications' => 'payload', 'dashboard_layouts' => 'widgets', 'audit_logs' => 'changes'] as $table => $column) {
            $this->assertNotContains(Schema::getColumnType($table, $column), ['float', 'double', 'real']);
        }
    }

    public function test_dashboard_layouts_support_role_default_and_user_override(): void
    {
        $roleDefault = DashboardLayout::factory()->create(['user_id' => null]);
        $override = DashboardLayout::factory()->create(['user_id' => User::factory()]);

        $this->assertNull($roleDefault->user_id);
        $this->assertInstanceOf(User::class, $override->fresh()->user);
    }

    public function test_audit_log_rows_reject_updates(): void
    {
        $log = AuditLog::factory()->create();

        $this->expectException(LogicException::class);

        $log->setAttribute('action', 'changed');
        $log->save();
    }

    public function test_audit_log_rows_reject_deletes(): void
    {
        $log = AuditLog::factory()->create();

        $this->expectException(LogicException::class);

        $log->delete();
    }

    public function test_login_history_records_are_written_by_factory(): void
    {
        $entry = LoginHistory::factory()->create(['logged_out_at' => null]);

        $this->assertNotNull(DB::table('login_history')->where('id', $entry->id)->value('logged_in_at'));
        $this->assertNull($entry->fresh()->logged_out_at);
        $this->assertTrue(NotificationPreference::factory()->create()->exists);
    }
}
