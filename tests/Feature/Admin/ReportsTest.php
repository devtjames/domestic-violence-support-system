<?php

namespace Tests\Feature\Admin;

use App\Models\AbuseType;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_filtered_case_reports_without_private_descriptions(): void
    {
        $admin = User::factory()->admin()->create();
        $officer = User::factory()->supportOfficer()->create(['name' => 'Report Officer']);
        $abuseType = AbuseType::factory()->create(['name' => 'Report Abuse Type']);
        $matchingCase = SupportCase::factory()->assignedTo($officer)->create([
            'abuse_type_id' => $abuseType->id,
            'subject' => 'Sensitive report subject marker',
            'description' => 'Sensitive report description marker',
            'safe_contact_instruction' => 'Sensitive report contact marker',
            'urgency' => SupportCase::URGENCY_HIGH,
            'status' => SupportCase::STATUS_IN_PROGRESS,
            'is_anonymous' => false,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);
        $hiddenCase = SupportCase::factory()->anonymous()->create([
            'subject' => 'Hidden report subject marker',
            'description' => 'Hidden report description marker',
            'urgency' => SupportCase::URGENCY_LOW,
            'status' => SupportCase::STATUS_RESOLVED,
            'resolved_at' => now()->subDay(),
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.index', [
            'date_from' => now()->subDays(3)->toDateString(),
            'date_to' => now()->toDateString(),
            'urgency' => SupportCase::URGENCY_HIGH,
            'source' => 'registered',
            'workflow' => 'pending',
            'assigned_officer_id' => $officer->id,
        ]));

        $response->assertOk();
        $response->assertSee('Case Reports');
        $response->assertSee($matchingCase->case_reference);
        $response->assertSee('Report Officer');
        $response->assertSee('Report Abuse Type');
        $response->assertSee('Pending Cases');
        $response->assertDontSee($hiddenCase->case_reference);
        $response->assertDontSee('Sensitive report subject marker');
        $response->assertDontSee('Sensitive report description marker');
        $response->assertDontSee('Sensitive report contact marker');
        $response->assertDontSee('Hidden report subject marker');
    }

    public function test_report_csv_export_uses_safe_fields_only(): void
    {
        $admin = User::factory()->admin()->create();
        $officer = User::factory()->supportOfficer()->create(['name' => 'CSV Officer']);
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'subject' => 'CSV sensitive subject marker',
            'description' => 'CSV sensitive description marker',
            'safe_contact_instruction' => 'CSV sensitive contact marker',
            'contact_email' => 'private-csv@example.test',
            'contact_phone' => '555-0111',
            'status' => SupportCase::STATUS_RESOLVED,
            'resolved_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.export', [
            'workflow' => 'resolved',
        ]));

        $response->assertOk();

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Case Reference', $csv);
        $this->assertStringContainsString('Submitted Date', $csv);
        $this->assertStringContainsString('Assigned Officer', $csv);
        $this->assertStringContainsString($case->case_reference, $csv);
        $this->assertStringContainsString('CSV Officer', $csv);
        $this->assertStringNotContainsString('CSV sensitive subject marker', $csv);
        $this->assertStringNotContainsString('CSV sensitive description marker', $csv);
        $this->assertStringNotContainsString('CSV sensitive contact marker', $csv);
        $this->assertStringNotContainsString('private-csv@example.test', $csv);
        $this->assertStringNotContainsString('555-0111', $csv);
    }

    public function test_reports_include_anonymous_registered_pending_resolved_and_officer_groupings(): void
    {
        $admin = User::factory()->admin()->create();
        $officer = User::factory()->supportOfficer()->create(['name' => 'Grouped Officer']);

        SupportCase::factory()->assignedTo($officer)->create([
            'status' => SupportCase::STATUS_IN_PROGRESS,
            'is_anonymous' => false,
        ]);
        SupportCase::factory()->anonymous()->create([
            'status' => SupportCase::STATUS_CLOSED,
            'resolved_at' => now(),
            'closed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertSee('Anonymous vs Registered');
        $response->assertSee('Pending vs Resolved');
        $response->assertSee('Cases by Support Officer');
        $response->assertSee('Grouped Officer');
        $response->assertSee('Unassigned');
    }

    public function test_report_filters_are_validated(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.reports.index', [
            'date_from' => now()->toDateString(),
            'date_to' => now()->subDay()->toDateString(),
            'status' => 'unsupported',
            'urgency' => 'urgent',
            'abuse_type_id' => 999999,
            'assigned_officer_id' => 999999,
            'source' => 'private',
            'workflow' => 'done',
        ]));

        $response->assertSessionHasErrors([
            'date_to',
            'status',
            'urgency',
            'abuse_type_id',
            'assigned_officer_id',
            'source',
            'workflow',
        ]);
    }

    public function test_non_admin_cannot_access_reports(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.reports.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.reports.export'))
            ->assertForbidden();
    }
}
