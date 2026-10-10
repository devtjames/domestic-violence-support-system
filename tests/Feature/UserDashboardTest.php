<?php

namespace Tests\Feature;

use App\Models\AbuseType;
use App\Models\AwarenessArticle;
use App\Models\CaseActivity;
use App\Models\CaseMessage;
use App\Models\CaseNote;
use App\Models\SupportCase;
use App\Models\SupportResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_user_dashboard_shows_owned_case_summary_and_public_content(): void
    {
        $user = User::factory()->create(['name' => 'Registered Demo']);
        $otherUser = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();
        $abuseType = AbuseType::factory()->create(['name' => 'Safety Planning']);

        $activeCase = SupportCase::factory()->create([
            'user_id' => $user->id,
            'assigned_officer_id' => $officer->id,
            'abuse_type_id' => $abuseType->id,
            'status' => SupportCase::STATUS_IN_PROGRESS,
            'urgency' => SupportCase::URGENCY_HIGH,
            'subject' => 'Owned sensitive subject marker',
            'description' => 'Owned sensitive description marker',
            'safe_contact_instruction' => 'Owned sensitive safe contact marker',
        ]);
        $resolvedCase = SupportCase::factory()->create([
            'user_id' => $user->id,
            'abuse_type_id' => $abuseType->id,
            'status' => SupportCase::STATUS_RESOLVED,
            'urgency' => SupportCase::URGENCY_MEDIUM,
        ]);
        $otherUserCase = SupportCase::factory()->create([
            'user_id' => $otherUser->id,
            'subject' => 'Other user sensitive subject marker',
        ]);

        CaseMessage::factory()->create([
            'support_case_id' => $activeCase->id,
            'sender_id' => $officer->id,
            'receiver_id' => $user->id,
            'message' => 'Owned sensitive private message marker',
            'read_at' => null,
        ]);
        CaseMessage::factory()->create([
            'support_case_id' => $otherUserCase->id,
            'sender_id' => $officer->id,
            'receiver_id' => $otherUser->id,
            'message' => 'Other user sensitive private message marker',
            'read_at' => null,
        ]);
        CaseNote::factory()->create([
            'support_case_id' => $activeCase->id,
            'user_id' => $officer->id,
            'note' => 'Owned sensitive internal note marker',
        ]);
        CaseActivity::factory()->create([
            'support_case_id' => $activeCase->id,
            'user_id' => $officer->id,
            'action_type' => CaseActivity::ACTION_STATUS_CHANGED,
            'description' => 'Owned sensitive activity description marker',
            'previous_value' => 'Sensitive previous value marker',
            'new_value' => 'Sensitive new value marker',
        ]);
        CaseActivity::factory()->create([
            'support_case_id' => $activeCase->id,
            'user_id' => $officer->id,
            'action_type' => CaseActivity::ACTION_INTERNAL_NOTE_ADDED,
            'description' => 'Owned internal note activity marker',
        ]);

        $publishedArticle = AwarenessArticle::factory()->published()->create(['title' => 'Published Safety Article']);
        AwarenessArticle::factory()->create(['title' => 'Draft Safety Article']);
        $activeResource = SupportResource::factory()->create(['organization_name' => 'Active Resource Service']);
        SupportResource::factory()->inactive()->create(['organization_name' => 'Inactive Resource Service']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Registered Demo');
        $response->assertSee('Request Support');
        $response->assertSee('Read Messages');
        $response->assertSee($activeCase->case_reference);
        $response->assertSee($resolvedCase->case_reference);
        $response->assertSee('Status changed');
        $response->assertSee($publishedArticle->title);
        $response->assertSee($activeResource->organization_name);
        $response->assertDontSee($otherUserCase->case_reference);
        $response->assertDontSee('Draft Safety Article');
        $response->assertDontSee('Inactive Resource Service');
        $response->assertViewHas('summaryCards', function (array $summaryCards): bool {
            $cards = collect($summaryCards)->pluck('value', 'label');

            return $cards['Active Cases'] === 1
                && $cards['Resolved Cases'] === 1
                && $cards['Unread Messages'] === 1
                && $cards['Total Cases'] === 2;
        });
        $response->assertDontSee('Owned sensitive subject marker');
        $response->assertDontSee('Owned sensitive description marker');
        $response->assertDontSee('Owned sensitive safe contact marker');
        $response->assertDontSee('Owned sensitive private message marker');
        $response->assertDontSee('Owned sensitive internal note marker');
        $response->assertDontSee('Owned sensitive activity description marker');
        $response->assertDontSee('Sensitive previous value marker');
        $response->assertDontSee('Sensitive new value marker');
        $response->assertDontSee('Owned internal note activity marker');
        $response->assertDontSee('Internal note added');
        $response->assertDontSee('Other user sensitive subject marker');
        $response->assertDontSee('Other user sensitive private message marker');
    }

    public function test_non_registered_user_cannot_access_registered_user_dashboard(): void
    {
        $officer = User::factory()->supportOfficer()->create();

        $this->actingAs($officer)
            ->get(route('dashboard'))
            ->assertForbidden();
    }
}
