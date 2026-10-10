<?php

namespace Tests\Feature\Admin;

use App\Models\CaseActivity;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_registered_and_anonymous_cases(): void
    {
        $admin = User::factory()->admin()->create();
        $registeredCase = SupportCase::factory()->create([
            'subject' => 'Registered case for admin',
        ]);
        $anonymousCase = SupportCase::factory()->anonymous()->create([
            'subject' => 'Anonymous case for admin',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.cases.index'));

        $response->assertOk();
        $response->assertSee($registeredCase->case_reference);
        $response->assertSee($anonymousCase->case_reference);
        $response->assertSee('Registered case for admin');
        $response->assertSee('Anonymous case for admin');
    }

    public function test_non_admin_cannot_access_admin_case_management(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.cases.index'))
            ->assertForbidden();
    }

    public function test_admin_can_assign_reassign_update_status_and_record_history(): void
    {
        $admin = User::factory()->admin()->create();
        $firstOfficer = User::factory()->supportOfficer()->create(['name' => 'First Officer']);
        $secondOfficer = User::factory()->supportOfficer()->create(['name' => 'Second Officer']);
        $case = SupportCase::factory()->create([
            'status' => SupportCase::STATUS_SUBMITTED,
            'urgency' => SupportCase::URGENCY_LOW,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.cases.update', $case), [
                'assigned_officer_id' => $firstOfficer->id,
                'urgency' => SupportCase::URGENCY_HIGH,
                'status' => SupportCase::STATUS_ASSIGNED,
                'resolution_summary' => '',
                'action_note' => 'Initial assignment for review.',
            ])
            ->assertRedirect(route('admin.cases.show', $case, false));

        $case->refresh();
        $this->assertSame($firstOfficer->id, $case->assigned_officer_id);
        $this->assertSame(SupportCase::URGENCY_HIGH, $case->urgency);
        $this->assertSame(SupportCase::STATUS_ASSIGNED, $case->status);

        $this->assertDatabaseHas('case_activities', [
            'support_case_id' => $case->id,
            'user_id' => $admin->id,
            'action_type' => CaseActivity::ACTION_CASE_ASSIGNED,
            'previous_value' => 'Unassigned',
            'new_value' => 'First Officer',
        ]);
        $this->assertDatabaseHas('case_activities', [
            'support_case_id' => $case->id,
            'action_type' => CaseActivity::ACTION_URGENCY_CHANGED,
            'previous_value' => SupportCase::URGENCY_LOW,
            'new_value' => SupportCase::URGENCY_HIGH,
        ]);
        $this->assertDatabaseHas('case_activities', [
            'support_case_id' => $case->id,
            'action_type' => CaseActivity::ACTION_STATUS_CHANGED,
            'previous_value' => SupportCase::STATUS_SUBMITTED,
            'new_value' => SupportCase::STATUS_ASSIGNED,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.cases.update', $case), [
                'assigned_officer_id' => $secondOfficer->id,
                'urgency' => SupportCase::URGENCY_HIGH,
                'status' => SupportCase::STATUS_RESOLVED,
                'resolution_summary' => 'The support request was reviewed and resolved for demonstration.',
                'action_note' => 'Resolved after reassignment.',
            ])
            ->assertSessionHas('status');

        $case->refresh();
        $this->assertSame($secondOfficer->id, $case->assigned_officer_id);
        $this->assertSame(SupportCase::STATUS_RESOLVED, $case->status);
        $this->assertNotNull($case->resolved_at);

        $this->assertDatabaseHas('case_activities', [
            'support_case_id' => $case->id,
            'action_type' => CaseActivity::ACTION_CASE_REASSIGNED,
            'previous_value' => 'First Officer',
            'new_value' => 'Second Officer',
        ]);
        $this->assertDatabaseHas('case_activities', [
            'support_case_id' => $case->id,
            'action_type' => CaseActivity::ACTION_CASE_RESOLVED,
            'previous_value' => SupportCase::STATUS_ASSIGNED,
            'new_value' => SupportCase::STATUS_RESOLVED,
        ]);
    }

    public function test_admin_case_update_requires_active_support_officer_and_resolution_summary(): void
    {
        $admin = User::factory()->admin()->create();
        $disabledOfficer = User::factory()->supportOfficer()->disabled()->create();
        $case = SupportCase::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.cases.update', $case), [
                'assigned_officer_id' => $disabledOfficer->id,
                'urgency' => SupportCase::URGENCY_MEDIUM,
                'status' => SupportCase::STATUS_RESOLVED,
                'resolution_summary' => '',
            ])
            ->assertSessionHasErrors(['assigned_officer_id', 'resolution_summary']);
    }
}
