<?php

namespace Tests\Feature;

use App\Models\AnonymousCaseAccess;
use App\Models\CaseActivity;
use App\Models\CaseNote;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalCaseNotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_officer_can_add_internal_note_to_authorized_case(): void
    {
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create();
        $noteText = 'Private staff-only assessment details.';

        $this->actingAs($officer)
            ->post(route('cases.notes.store', $case), [
                'note' => $noteText,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('case_notes', [
            'support_case_id' => $case->id,
            'user_id' => $officer->id,
            'note' => $noteText,
        ]);
        $this->assertDatabaseHas('case_activities', [
            'support_case_id' => $case->id,
            'user_id' => $officer->id,
            'action_type' => CaseActivity::ACTION_INTERNAL_NOTE_ADDED,
            'description' => 'Internal case note added.',
            'previous_value' => null,
            'new_value' => null,
        ]);
        $this->assertDatabaseMissing('case_activities', [
            'support_case_id' => $case->id,
            'description' => $noteText,
        ]);
    }

    public function test_admin_can_add_and_view_internal_notes(): void
    {
        $admin = User::factory()->admin()->create();
        $case = SupportCase::factory()->create();

        $this->actingAs($admin)
            ->post(route('cases.notes.store', $case), [
                'note' => 'Administrative review note.',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->get(route('admin.cases.show', $case))
            ->assertOk()
            ->assertSee('Internal Case Notes')
            ->assertSee('Administrative review note.');
    }

    public function test_officer_cannot_add_note_to_unauthorized_case(): void
    {
        $officer = User::factory()->supportOfficer()->create();
        $otherOfficer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($otherOfficer)->create();

        $this->actingAs($officer)
            ->post(route('cases.notes.store', $case), [
                'note' => 'This should not be stored.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('case_notes', 0);
    }

    public function test_registered_user_cannot_create_or_view_internal_notes(): void
    {
        $user = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'user_id' => $user->id,
        ]);
        CaseNote::factory()->create([
            'support_case_id' => $case->id,
            'user_id' => $officer->id,
            'note' => 'Staff-only safety planning note.',
        ]);

        $this->actingAs($user)
            ->post(route('cases.notes.store', $case), [
                'note' => 'User should not add internal notes.',
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('cases.show', $case))
            ->assertOk()
            ->assertDontSee('Internal Case Notes')
            ->assertDontSee('Staff-only safety planning note.');
    }

    public function test_anonymous_tracking_never_shows_internal_notes(): void
    {
        $case = SupportCase::factory()->anonymous()->create([
            'description' => 'Sensitive anonymous case description.',
        ]);
        $case->anonymousAccess()->create([
            'access_code_hash' => AnonymousCaseAccess::hashAccessCode('ABCDEFGH'),
        ]);
        CaseNote::factory()->create([
            'support_case_id' => $case->id,
            'note' => 'Internal anonymous case handling note.',
        ]);

        $this->post(route('anonymous.track.lookup'), [
            'case_reference' => $case->case_reference,
            'access_code' => 'ABCDEFGH',
        ])
            ->assertOk()
            ->assertDontSee('Internal Case Notes')
            ->assertDontSee('Internal anonymous case handling note.');
    }

    public function test_internal_note_validation_requires_meaningful_note_text(): void
    {
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create();

        $this->actingAs($officer)
            ->post(route('cases.notes.store', $case), [
                'note' => 'no',
            ])
            ->assertSessionHasErrors('note');
    }
}
