<?php

namespace Tests\Feature\Officer;

use App\Models\CaseActivity;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_can_view_assigned_and_unassigned_open_cases_only(): void
    {
        $officer = User::factory()->supportOfficer()->create();
        $otherOfficer = User::factory()->supportOfficer()->create();

        $assigned = SupportCase::factory()->assignedTo($officer)->create([
            'subject' => 'Assigned visible case',
        ]);
        $unassigned = SupportCase::factory()->create([
            'subject' => 'Unassigned visible case',
            'status' => SupportCase::STATUS_SUBMITTED,
        ]);
        $otherAssigned = SupportCase::factory()->assignedTo($otherOfficer)->create([
            'subject' => 'Other officer case',
        ]);
        $closedUnassigned = SupportCase::factory()->create([
            'subject' => 'Closed queue case',
            'status' => SupportCase::STATUS_CLOSED,
        ]);

        $response = $this->actingAs($officer)->get(route('officer.cases.index'));

        $response->assertOk();
        $response->assertSee($assigned->case_reference);
        $response->assertSee($unassigned->case_reference);
        $response->assertDontSee($otherAssigned->case_reference);
        $response->assertDontSee($closedUnassigned->case_reference);
    }

    public function test_officer_cannot_view_case_assigned_to_another_officer(): void
    {
        $officer = User::factory()->supportOfficer()->create();
        $otherOfficer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($otherOfficer)->create();

        $this->actingAs($officer)
            ->get(route('officer.cases.show', $case))
            ->assertForbidden();
    }

    public function test_regular_user_cannot_access_officer_case_management(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('officer.cases.index'))
            ->assertForbidden();
    }

    public function test_officer_can_accept_unassigned_case_and_history_is_recorded(): void
    {
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->create([
            'status' => SupportCase::STATUS_SUBMITTED,
        ]);

        $this->actingAs($officer)
            ->patch(route('officer.cases.accept', $case))
            ->assertRedirect(route('officer.cases.show', $case, false));

        $case->refresh();
        $this->assertSame($officer->id, $case->assigned_officer_id);
        $this->assertSame(SupportCase::STATUS_ASSIGNED, $case->status);

        $this->assertDatabaseHas('case_activities', [
            'support_case_id' => $case->id,
            'user_id' => $officer->id,
            'action_type' => CaseActivity::ACTION_CASE_ASSIGNED,
            'previous_value' => 'Unassigned',
            'new_value' => $officer->name,
        ]);
    }

    public function test_officer_can_update_owned_case_status_urgency_and_resolution(): void
    {
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'urgency' => SupportCase::URGENCY_LOW,
            'status' => SupportCase::STATUS_ASSIGNED,
        ]);

        $this->actingAs($officer)
            ->patch(route('officer.cases.update', $case), [
                'urgency' => SupportCase::URGENCY_CRITICAL,
                'status' => SupportCase::STATUS_RESOLVED,
                'resolution_summary' => 'Reviewed and resolved for demonstration.',
                'action_note' => 'Marked resolved after review.',
            ])
            ->assertRedirect(route('officer.cases.show', $case, false));

        $case->refresh();
        $this->assertSame(SupportCase::URGENCY_CRITICAL, $case->urgency);
        $this->assertSame(SupportCase::STATUS_RESOLVED, $case->status);
        $this->assertNotNull($case->resolved_at);

        $this->assertDatabaseHas('case_activities', [
            'support_case_id' => $case->id,
            'user_id' => $officer->id,
            'action_type' => CaseActivity::ACTION_URGENCY_CHANGED,
            'previous_value' => SupportCase::URGENCY_LOW,
            'new_value' => SupportCase::URGENCY_CRITICAL,
        ]);
        $this->assertDatabaseHas('case_activities', [
            'support_case_id' => $case->id,
            'action_type' => CaseActivity::ACTION_CASE_RESOLVED,
            'previous_value' => SupportCase::STATUS_ASSIGNED,
            'new_value' => SupportCase::STATUS_RESOLVED,
        ]);
    }

    public function test_officer_update_validation_requires_resolution_summary(): void
    {
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create();

        $this->actingAs($officer)
            ->patch(route('officer.cases.update', $case), [
                'urgency' => SupportCase::URGENCY_MEDIUM,
                'status' => SupportCase::STATUS_CLOSED,
                'resolution_summary' => '',
            ])
            ->assertSessionHasErrors('resolution_summary');
    }
}
