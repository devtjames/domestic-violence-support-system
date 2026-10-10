<?php

namespace Tests\Feature;

use App\Models\AbuseType;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportCaseSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_user_can_submit_support_case(): void
    {
        $user = User::factory()->create();
        $abuseType = AbuseType::factory()->create([
            'name' => 'Digital Abuse',
            'slug' => 'digital-abuse',
        ]);

        $response = $this->actingAs($user)->post(route('cases.store'), [
            'abuse_type_id' => $abuseType->id,
            'subject' => 'Need help with account monitoring',
            'description' => 'I am worried that someone may be monitoring my accounts and I need private support planning guidance.',
            'preferred_contact_method' => SupportCase::CONTACT_PLATFORM_MESSAGE,
            'safe_contact_time' => 'Weekday afternoons',
            'safe_contact_instruction' => 'Please avoid sending message previews with sensitive details.',
            'urgency' => SupportCase::URGENCY_MEDIUM,
        ]);

        $case = SupportCase::query()->firstOrFail();

        $response->assertRedirect(route('cases.show', $case, false));
        $this->assertMatchesRegularExpression('/^DV-\d{4}-\d{6}$/', $case->case_reference);
        $this->assertSame($user->id, $case->user_id);
        $this->assertFalse($case->is_anonymous);
        $this->assertSame(SupportCase::STATUS_SUBMITTED, $case->status);
        $this->assertNull($case->assigned_officer_id);
    }

    public function test_case_references_are_unique_and_sequential_for_year(): void
    {
        $user = User::factory()->create();
        $abuseType = AbuseType::factory()->create();

        $first = SupportCase::factory()->create([
            'user_id' => $user->id,
            'abuse_type_id' => $abuseType->id,
        ]);

        $second = SupportCase::factory()->create([
            'user_id' => $user->id,
            'abuse_type_id' => $abuseType->id,
        ]);

        $this->assertNotSame($first->case_reference, $second->case_reference);
        $this->assertStringEndsWith('000001', $first->case_reference);
        $this->assertStringEndsWith('000002', $second->case_reference);
    }

    public function test_user_can_only_view_their_own_case(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $case = SupportCase::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->get(route('cases.show', $case))
            ->assertOk()
            ->assertSee($case->case_reference);

        $this->actingAs($otherUser)
            ->get(route('cases.show', $case))
            ->assertForbidden();
    }

    public function test_user_case_index_only_lists_their_cases(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $visible = SupportCase::factory()->create([
            'user_id' => $user->id,
            'subject' => 'Visible private request',
        ]);

        $hidden = SupportCase::factory()->create([
            'user_id' => $otherUser->id,
            'subject' => 'Hidden private request',
        ]);

        $this->actingAs($user)
            ->get(route('cases.index'))
            ->assertOk()
            ->assertSee($visible->case_reference)
            ->assertSee('Visible private request')
            ->assertDontSee($hidden->case_reference)
            ->assertDontSee('Hidden private request');
    }

    public function test_only_registered_user_role_can_access_case_submission_routes(): void
    {
        $officer = User::factory()->supportOfficer()->create();
        $admin = User::factory()->admin()->create();

        $this->get(route('cases.create'))->assertRedirect(route('login', absolute: false));

        $this->actingAs($officer)
            ->get(route('cases.create'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('cases.index'))
            ->assertForbidden();
    }

    public function test_support_case_submission_validation(): void
    {
        $user = User::factory()->create();
        $inactiveAbuseType = AbuseType::factory()->inactive()->create();

        $response = $this->actingAs($user)->post(route('cases.store'), [
            'abuse_type_id' => $inactiveAbuseType->id,
            'subject' => 'Bad',
            'description' => 'Too short',
            'preferred_contact_method' => 'invalid',
            'safe_contact_time' => str_repeat('a', 121),
            'safe_contact_instruction' => str_repeat('b', 1001),
            'urgency' => 'urgent',
        ]);

        $response->assertSessionHasErrors([
            'abuse_type_id',
            'subject',
            'description',
            'preferred_contact_method',
            'safe_contact_time',
            'safe_contact_instruction',
            'urgency',
        ]);
    }
}
