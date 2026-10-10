<?php

namespace Tests\Feature;

use App\Models\AbuseType;
use App\Models\AwarenessArticle;
use App\Models\AwarenessCategory;
use App\Models\CaseActivity;
use App\Models\CaseNote;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MainWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_role_protection_and_disabled_account_status(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin-workflow@example.test']);
        $officer = User::factory()->supportOfficer()->create(['email' => 'officer-workflow@example.test']);
        $user = User::factory()->create(['email' => 'user-workflow@example.test']);
        $disabledUser = User::factory()->disabled()->create(['email' => 'disabled-workflow@example.test']);

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard', absolute: false));

        $this->post(route('logout'))->assertRedirect('/');

        $this->post(route('login'), [
            'email' => $officer->email,
            'password' => 'password',
        ])->assertRedirect(route('officer.dashboard', absolute: false));

        $this->post(route('logout'))->assertRedirect('/');

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actingAs($officer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->post(route('logout'))->assertRedirect('/');

        $this->post(route('login'), [
            'email' => $disabledUser->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');
    }

    public function test_registered_case_submission_assignment_and_access_boundaries(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create(['name' => 'Assigned Workflow Officer']);
        $otherOfficer = User::factory()->supportOfficer()->create();
        $abuseType = AbuseType::factory()->create(['name' => 'Workflow Abuse Type']);

        $this->actingAs($user)
            ->post(route('cases.store'), $this->registeredCasePayload($abuseType))
            ->assertRedirect();

        $case = SupportCase::query()->firstOrFail();

        $this->assertMatchesRegularExpression('/^DV-\d{4}-\d{6}$/', $case->case_reference);
        $this->assertSame($user->id, $case->user_id);
        $this->assertFalse($case->is_anonymous);
        $this->assertSame(SupportCase::STATUS_SUBMITTED, $case->status);

        $this->actingAs($otherUser)
            ->get(route('cases.show', $case))
            ->assertForbidden();

        $this->actingAs($admin)
            ->patch(route('admin.cases.update', $case), [
                'assigned_officer_id' => $officer->id,
                'urgency' => SupportCase::URGENCY_HIGH,
                'status' => SupportCase::STATUS_ASSIGNED,
                'resolution_summary' => '',
                'action_note' => 'Workflow assignment note.',
            ])
            ->assertRedirect(route('admin.cases.show', $case, false));

        $case->refresh();

        $this->assertSame($officer->id, $case->assigned_officer_id);
        $this->assertSame(SupportCase::STATUS_ASSIGNED, $case->status);
        $this->assertSame(SupportCase::URGENCY_HIGH, $case->urgency);

        $this->actingAs($officer)
            ->get(route('officer.cases.show', $case))
            ->assertOk()
            ->assertSee($case->case_reference);

        $this->actingAs($otherOfficer)
            ->get(route('officer.cases.show', $case))
            ->assertForbidden();

        $this->assertDatabaseHas('case_activities', [
            'support_case_id' => $case->id,
            'action_type' => CaseActivity::ACTION_CASE_ASSIGNED,
            'new_value' => 'Assigned Workflow Officer',
        ]);
    }

    public function test_private_messaging_and_internal_notes_follow_visibility_rules(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();
        $otherOfficer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'user_id' => $user->id,
            'status' => SupportCase::STATUS_ASSIGNED,
        ]);

        $this->actingAs($user)
            ->post(route('cases.messages.store', $case), [
                'message' => 'Workflow user reply that belongs only to this case.',
            ])
            ->assertRedirect();

        $case->refresh();

        $this->assertSame(SupportCase::STATUS_IN_PROGRESS, $case->status);
        $this->assertDatabaseHas('case_messages', [
            'support_case_id' => $case->id,
            'sender_id' => $user->id,
            'receiver_id' => $officer->id,
            'message' => 'Workflow user reply that belongs only to this case.',
        ]);

        $this->actingAs($officer)
            ->post(route('cases.notes.store', $case), [
                'note' => 'Workflow internal note marker for staff only.',
            ])
            ->assertRedirect();

        $this->actingAs($officer)
            ->post(route('cases.messages.store', $case), [
                'message' => 'Workflow officer reply visible to the registered user.',
            ])
            ->assertRedirect();

        $this->actingAs($otherOfficer)
            ->post(route('cases.messages.store', $case), [
                'message' => 'Unauthorized officer message should fail.',
            ])
            ->assertForbidden();

        $this->assertSame(SupportCase::STATUS_AWAITING_USER, $case->refresh()->status);
        $this->assertDatabaseHas('case_activities', [
            'support_case_id' => $case->id,
            'action_type' => CaseActivity::ACTION_INTERNAL_NOTE_ADDED,
        ]);

        $this->actingAs($user)
            ->get(route('cases.show', $case))
            ->assertOk()
            ->assertSee('Workflow officer reply visible to the registered user.')
            ->assertDontSee('Workflow internal note marker for staff only.');

        $this->actingAs($admin)
            ->get(route('admin.cases.show', $case))
            ->assertOk()
            ->assertSee('Workflow internal note marker for staff only.');
    }

    public function test_anonymous_submission_tracking_and_privacy_boundaries(): void
    {
        $abuseType = AbuseType::factory()->create();

        $this->post(route('anonymous.store'), [
            'anonymous_nickname' => 'Workflow Visitor',
            'abuse_type_id' => $abuseType->id,
            'subject' => 'Anonymous workflow support request',
            'description' => 'Anonymous workflow sensitive description marker that must not appear on tracking output.',
            'preferred_contact_method' => SupportCase::CONTACT_EMAIL,
            'contact_email' => 'anonymous-workflow@example.test',
            'contact_phone' => '',
            'safe_contact_instruction' => 'Anonymous workflow contact marker.',
            'urgency' => SupportCase::URGENCY_MEDIUM,
        ])->assertRedirect(route('anonymous.confirmation', absolute: false));

        $case = SupportCase::query()->where('is_anonymous', true)->firstOrFail();
        $accessCode = session('anonymous_access_code');
        $access = $case->anonymousAccess()->firstOrFail();

        CaseNote::factory()->create([
            'support_case_id' => $case->id,
            'note' => 'Workflow anonymous internal note marker.',
        ]);

        $this->assertNotSame($accessCode, $access->access_code_hash);
        $this->assertTrue($access->matches($accessCode));

        $this->post(route('anonymous.track.lookup'), [
            'case_reference' => $case->case_reference,
            'access_code' => 'BADCODE1',
        ])->assertSessionHasErrors('case_reference');

        $this->post(route('anonymous.track.lookup'), [
            'case_reference' => $case->case_reference,
            'access_code' => $accessCode,
        ])
            ->assertOk()
            ->assertSee($case->case_reference)
            ->assertSee('Submitted')
            ->assertDontSee('Anonymous workflow sensitive description marker')
            ->assertDontSee('anonymous-workflow@example.test')
            ->assertDontSee('Anonymous workflow contact marker')
            ->assertDontSee('Workflow anonymous internal note marker');
    }

    public function test_article_publishing_controls_public_visibility(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $category = AwarenessCategory::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.awareness.articles.store'), $this->articlePayload($category))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.awareness.articles.store'), $this->articlePayload($category, [
                'title' => 'Workflow Safety Planning Article',
                'slug' => 'workflow-safety-planning-article',
                'status' => AwarenessArticle::STATUS_DRAFT,
                'published_at' => '',
            ]))
            ->assertRedirect();

        $article = AwarenessArticle::query()
            ->where('slug', 'workflow-safety-planning-article')
            ->firstOrFail();

        $this->get(route('public.awareness.show', $article->slug))
            ->assertNotFound();

        $this->actingAs($admin)
            ->patch(route('admin.awareness.articles.publish', $article))
            ->assertRedirect();

        $this->get(route('public.awareness.show', $article->slug))
            ->assertOk()
            ->assertSee('Workflow Safety Planning Article');

        $this->actingAs($admin)
            ->patch(route('admin.awareness.articles.archive', $article->refresh()))
            ->assertRedirect();

        $this->get(route('public.awareness.show', $article->slug))
            ->assertNotFound();
    }

    /**
     * @return array<string, mixed>
     */
    private function registeredCasePayload(AbuseType $abuseType): array
    {
        return [
            'abuse_type_id' => $abuseType->id,
            'subject' => 'Workflow support request',
            'description' => 'Workflow support request description with enough detail to pass validation.',
            'preferred_contact_method' => SupportCase::CONTACT_PLATFORM_MESSAGE,
            'safe_contact_time' => 'Weekday afternoons',
            'safe_contact_instruction' => 'Use neutral platform messages.',
            'urgency' => SupportCase::URGENCY_MEDIUM,
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function articlePayload(AwarenessCategory $category, array $overrides = []): array
    {
        return array_merge([
            'title' => 'Workflow Awareness Article',
            'slug' => 'workflow-awareness-article',
            'awareness_category_id' => $category->id,
            'short_description' => 'This workflow article summary is long enough for validation.',
            'content' => 'This workflow article content is deliberately educational, calm, and long enough for the validation rules in the awareness publishing flow.',
            'status' => AwarenessArticle::STATUS_PUBLISHED,
            'published_at' => now()->toDateString(),
        ], $overrides);
    }
}
