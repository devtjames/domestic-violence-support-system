<?php

namespace Tests\Feature;

use App\Models\AnonymousCaseAccess;
use App\Models\CaseActivity;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseActivityHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_complete_case_activity_history(): void
    {
        $admin = User::factory()->admin()->create();
        $officer = User::factory()->supportOfficer()->create(['name' => 'History Officer']);
        $case = SupportCase::factory()->create([
            'status' => SupportCase::STATUS_SUBMITTED,
            'urgency' => SupportCase::URGENCY_LOW,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.cases.update', $case), [
                'assigned_officer_id' => $officer->id,
                'urgency' => SupportCase::URGENCY_HIGH,
                'status' => SupportCase::STATUS_REFERRED,
                'resolution_summary' => '',
                'action_note' => '',
            ]);

        $response = $this->actingAs($admin)->get(route('admin.cases.show', $case));

        $response
            ->assertOk()
            ->assertSee('Case Activity History')
            ->assertSee('Case assigned')
            ->assertSee('Urgency changed')
            ->assertSee('Case referred')
            ->assertSee('History Officer');
    }

    public function test_reopening_a_closed_case_is_recorded_and_clears_terminal_dates(): void
    {
        $admin = User::factory()->admin()->create();
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'status' => SupportCase::STATUS_CLOSED,
            'urgency' => SupportCase::URGENCY_MEDIUM,
            'resolved_at' => now()->subDay(),
            'closed_at' => now()->subHour(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.cases.update', $case), [
                'assigned_officer_id' => $officer->id,
                'urgency' => SupportCase::URGENCY_MEDIUM,
                'status' => SupportCase::STATUS_IN_PROGRESS,
                'resolution_summary' => '',
                'action_note' => 'Reopened after new information.',
            ])
            ->assertRedirect(route('admin.cases.show', $case, false));

        $case->refresh();

        $this->assertSame(SupportCase::STATUS_IN_PROGRESS, $case->status);
        $this->assertNull($case->resolved_at);
        $this->assertNull($case->closed_at);
        $this->assertDatabaseHas('case_activities', [
            'support_case_id' => $case->id,
            'user_id' => $admin->id,
            'action_type' => CaseActivity::ACTION_CASE_REOPENED,
            'previous_value' => SupportCase::STATUS_CLOSED,
            'new_value' => SupportCase::STATUS_IN_PROGRESS,
        ]);
    }

    public function test_officer_reopening_authorized_case_is_recorded(): void
    {
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'status' => SupportCase::STATUS_RESOLVED,
            'urgency' => SupportCase::URGENCY_MEDIUM,
            'resolved_at' => now()->subDay(),
        ]);

        $this->actingAs($officer)
            ->patch(route('officer.cases.update', $case), [
                'urgency' => SupportCase::URGENCY_MEDIUM,
                'status' => SupportCase::STATUS_UNDER_REVIEW,
                'resolution_summary' => '',
                'action_note' => '',
            ])
            ->assertRedirect(route('officer.cases.show', $case, false));

        $this->assertDatabaseHas('case_activities', [
            'support_case_id' => $case->id,
            'user_id' => $officer->id,
            'action_type' => CaseActivity::ACTION_CASE_REOPENED,
            'previous_value' => SupportCase::STATUS_RESOLVED,
            'new_value' => SupportCase::STATUS_UNDER_REVIEW,
        ]);
    }

    public function test_case_activity_history_is_not_visible_to_registered_users_or_anonymous_visitors(): void
    {
        $user = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'user_id' => $user->id,
        ]);
        $case->recordActivity(
            CaseActivity::ACTION_INTERNAL_NOTE_ADDED,
            'Internal case note added.',
            $officer
        );

        $this->actingAs($user)
            ->get(route('cases.show', $case))
            ->assertOk()
            ->assertDontSee('Case Activity History')
            ->assertDontSee('Internal case note added.');

        $anonymousCase = SupportCase::factory()->anonymous()->create();
        $anonymousCase->anonymousAccess()->create([
            'access_code_hash' => AnonymousCaseAccess::hashAccessCode('ABCDEFGH'),
        ]);
        $anonymousCase->recordActivity(
            CaseActivity::ACTION_CASE_REFERRED,
            'Internal referral activity.',
            $officer
        );

        $this->post(route('anonymous.track.lookup'), [
            'case_reference' => $anonymousCase->case_reference,
            'access_code' => 'ABCDEFGH',
        ])
            ->assertOk()
            ->assertDontSee('Case Activity History')
            ->assertDontSee('Internal referral activity.');
    }

    public function test_reply_and_internal_note_actions_are_recorded_without_sensitive_content(): void
    {
        $user = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'user_id' => $user->id,
            'status' => SupportCase::STATUS_IN_PROGRESS,
        ]);

        $this->actingAs($officer)->post(route('cases.messages.store', $case), [
            'message' => 'Sensitive private message body.',
        ]);
        $this->actingAs($officer)->post(route('cases.notes.store', $case), [
            'note' => 'Sensitive internal note body.',
        ]);

        $this->assertDatabaseHas('case_activities', [
            'support_case_id' => $case->id,
            'action_type' => CaseActivity::ACTION_OFFICER_RESPONDED,
            'description' => 'Support officer sent a private case message.',
        ]);
        $this->assertDatabaseHas('case_activities', [
            'support_case_id' => $case->id,
            'action_type' => CaseActivity::ACTION_INTERNAL_NOTE_ADDED,
            'description' => 'Internal case note added.',
        ]);
        $this->assertDatabaseMissing('case_activities', [
            'support_case_id' => $case->id,
            'description' => 'Sensitive private message body.',
        ]);
        $this->assertDatabaseMissing('case_activities', [
            'support_case_id' => $case->id,
            'description' => 'Sensitive internal note body.',
        ]);
    }
}
