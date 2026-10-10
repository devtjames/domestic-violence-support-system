<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_officer_logins_are_audited_with_request_context(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'audit-admin@example.test']);
        $officer = User::factory()->supportOfficer()->create(['email' => 'audit-officer@example.test']);

        $this->withHeader('User-Agent', 'AuditTestBrowser/1.0')
            ->post(route('login'), [
                'email' => $admin->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('admin.dashboard', absolute: false));

        $this->post(route('logout'));

        $this->withHeader('User-Agent', 'AuditOfficerBrowser/1.0')
            ->post(route('login'), [
                'email' => $officer->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('officer.dashboard', absolute: false));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'module' => AuditLog::MODULE_AUTH,
            'action' => 'admin_login',
            'description' => 'Administrator signed in.',
            'user_agent' => 'AuditTestBrowser/1.0',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $officer->id,
            'module' => AuditLog::MODULE_AUTH,
            'action' => 'officer_login',
            'description' => 'Support officer signed in.',
            'user_agent' => 'AuditOfficerBrowser/1.0',
        ]);
    }

    public function test_admin_account_and_officer_management_actions_are_audited(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.deactivate', $user))
            ->assertSessionHas('status');

        $this->actingAs($admin)
            ->post(route('admin.support-officers.store'), [
                'name' => 'Audit Officer',
                'email' => 'audit.officer@example.test',
                'phone' => '555-0199',
                'gender' => 'prefer_not_to_say',
                'staff_id' => 'SO-AUDIT',
                'specialization' => 'General Support',
                'account_status' => User::STATUS_ACTIVE,
                'date_joined' => now()->toDateString(),
                'password' => 'StrongPassword123!',
                'password_confirmation' => 'StrongPassword123!',
            ])
            ->assertRedirect();

        $officer = User::query()->where('email', 'audit.officer@example.test')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'module' => AuditLog::MODULE_USERS,
            'action' => 'user_deactivated',
            'auditable_type' => $user->getMorphClass(),
            'auditable_id' => $user->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'module' => AuditLog::MODULE_SUPPORT_OFFICERS,
            'action' => 'officer_created',
            'auditable_type' => $officer->getMorphClass(),
            'auditable_id' => $officer->id,
        ]);

        $payload = json_encode(AuditLog::query()->where('action', 'officer_created')->firstOrFail()->metadata);
        $this->assertStringNotContainsString('StrongPassword123!', $payload);
    }

    public function test_case_workflow_and_internal_support_actions_are_audited_without_sensitive_content(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->create([
            'user_id' => $user->id,
            'subject' => 'Sensitive audit subject marker',
            'description' => 'Sensitive audit description marker',
            'safe_contact_instruction' => 'Sensitive audit contact marker',
            'status' => SupportCase::STATUS_SUBMITTED,
            'urgency' => SupportCase::URGENCY_LOW,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.cases.update', $case), [
                'assigned_officer_id' => $officer->id,
                'urgency' => SupportCase::URGENCY_HIGH,
                'status' => SupportCase::STATUS_ASSIGNED,
                'resolution_summary' => '',
                'action_note' => 'Sensitive audit action note marker',
            ])
            ->assertRedirect(route('admin.cases.show', $case, false));

        $this->actingAs($officer)
            ->post(route('cases.notes.store', $case), [
                'note' => 'Sensitive internal note marker',
            ])
            ->assertRedirect();

        $this->actingAs($officer)
            ->post(route('cases.messages.store', $case), [
                'message' => 'Sensitive private message marker',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'module' => AuditLog::MODULE_CASES,
            'action' => 'case_assigned',
            'auditable_id' => $case->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'module' => AuditLog::MODULE_CASES,
            'action' => 'case_status_changed',
            'auditable_id' => $case->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'module' => AuditLog::MODULE_CASES,
            'action' => 'internal_note_added',
            'auditable_id' => $case->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'module' => AuditLog::MODULE_CASES,
            'action' => 'officer_reply_sent',
            'auditable_id' => $case->id,
        ]);

        $payload = AuditLog::query()->pluck('description')
            ->merge(AuditLog::query()->pluck('metadata')->map(fn ($metadata) => json_encode($metadata)))
            ->implode(' ');

        $this->assertStringNotContainsString('Sensitive audit subject marker', $payload);
        $this->assertStringNotContainsString('Sensitive audit description marker', $payload);
        $this->assertStringNotContainsString('Sensitive audit contact marker', $payload);
        $this->assertStringNotContainsString('Sensitive audit action note marker', $payload);
        $this->assertStringNotContainsString('Sensitive internal note marker', $payload);
        $this->assertStringNotContainsString('Sensitive private message marker', $payload);
    }

    public function test_admin_can_view_filter_and_inspect_read_only_audit_logs(): void
    {
        $admin = User::factory()->admin()->create();
        $officer = User::factory()->supportOfficer()->create(['name' => 'Filter Officer']);
        $visible = AuditLog::query()->create([
            'user_id' => $officer->id,
            'module' => AuditLog::MODULE_CASES,
            'action' => 'case_status_changed',
            'description' => 'Support case status changed for DV-2026-000111.',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'AuditBrowser/1.0',
            'metadata' => ['previous_status' => 'assigned', 'new_status' => 'in_progress'],
        ]);
        AuditLog::query()->create([
            'user_id' => $admin->id,
            'module' => AuditLog::MODULE_RESOURCES,
            'action' => 'resource_updated',
            'description' => 'Support resource updated.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index', [
                'module' => AuditLog::MODULE_CASES,
                'actor' => $officer->id,
            ]))
            ->assertOk()
            ->assertSee('Filter Officer')
            ->assertSee('Case Status Changed')
            ->assertSee('DV-2026-000111')
            ->assertDontSee('Support resource updated.')
            ->assertDontSee('Edit Audit Log');

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.show', $visible))
            ->assertOk()
            ->assertSee('AuditBrowser/1.0')
            ->assertSee('Previous Status')
            ->assertDontSee('Save Changes');

        $this->actingAs($admin)
            ->get('/admin/audit-logs/'.$visible->id.'/edit')
            ->assertNotFound();
    }

    public function test_audit_log_filters_validate_and_non_admins_are_blocked(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index', [
                'module' => 'unknown',
                'actor' => 999999,
                'date_from' => now()->toDateString(),
                'date_to' => now()->subDay()->toDateString(),
            ]))
            ->assertSessionHasErrors(['module', 'actor', 'date_to']);

        $this->actingAs($user)
            ->get(route('admin.audit-logs.index'))
            ->assertForbidden();
    }
}
