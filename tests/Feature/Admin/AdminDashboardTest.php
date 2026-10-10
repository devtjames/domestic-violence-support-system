<?php

namespace Tests\Feature\Admin;

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

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_aggregate_statistics_and_reports(): void
    {
        $admin = User::factory()->admin()->create();
        $registeredUser = User::factory()->create();
        User::factory()->count(2)->create();
        $officer = User::factory()->supportOfficer()->create(['name' => 'Officer Demo']);
        $physicalAbuse = AbuseType::factory()->create(['name' => 'Physical Abuse']);
        $digitalAbuse = AbuseType::factory()->create(['name' => 'Digital Abuse']);

        SupportCase::factory()->create([
            'user_id' => $registeredUser->id,
            'abuse_type_id' => $physicalAbuse->id,
            'status' => SupportCase::STATUS_UNDER_REVIEW,
            'urgency' => SupportCase::URGENCY_CRITICAL,
            'created_at' => now()->subMonth(),
        ]);
        SupportCase::factory()->anonymous()->create([
            'abuse_type_id' => $digitalAbuse->id,
            'assigned_officer_id' => $officer->id,
            'status' => SupportCase::STATUS_IN_PROGRESS,
            'urgency' => SupportCase::URGENCY_HIGH,
        ]);
        SupportCase::factory()->create([
            'user_id' => $registeredUser->id,
            'abuse_type_id' => $physicalAbuse->id,
            'status' => SupportCase::STATUS_RESOLVED,
            'urgency' => SupportCase::URGENCY_MEDIUM,
        ]);
        AwarenessArticle::factory()->published()->create();
        AwarenessArticle::factory()->create();
        SupportResource::factory()->create();
        SupportResource::factory()->inactive()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Admin Dashboard');
        $response->assertSee('Cases by Month');
        $response->assertSee('Cases by Abuse Type');
        $response->assertSee('cases-by-status-chart');
        $response->assertSee('Anonymous vs Registered Cases');
        $response->assertViewHas('summaryCards', function (array $summaryCards): bool {
            $cards = collect($summaryCards)->pluck('value', 'label');

            return $cards['Total Users'] === 3
                && $cards['Support Officers'] === 1
                && $cards['Total Cases'] === 3
                && $cards['Open Cases'] === 2
                && $cards['Under Review'] === 1
                && $cards['In Progress'] === 1
                && $cards['Resolved Cases'] === 1
                && $cards['Anonymous Cases'] === 1
                && $cards['Registered Cases'] === 2
                && $cards['High-Priority Cases'] === 2
                && $cards['Published Articles'] === 1
                && $cards['Support Resources'] === 1;
        });
        $response->assertViewHas('caseTypeCounts', fn (array $caseTypeCounts): bool => $caseTypeCounts['Physical Abuse'] === 2
            && $caseTypeCounts['Digital Abuse'] === 1);
    }

    public function test_admin_dashboard_does_not_expose_sensitive_case_content(): void
    {
        $admin = User::factory()->admin()->create();
        $officer = User::factory()->supportOfficer()->create();
        $user = User::factory()->create();
        $case = SupportCase::factory()->create([
            'user_id' => $user->id,
            'subject' => 'Sensitive dashboard subject leak marker',
            'description' => 'Sensitive dashboard description leak marker',
            'safe_contact_instruction' => 'Sensitive safe contact leak marker',
        ]);

        CaseMessage::factory()->create([
            'support_case_id' => $case->id,
            'sender_id' => $user->id,
            'receiver_id' => $officer->id,
            'message' => 'Sensitive private message leak marker',
        ]);
        CaseNote::factory()->create([
            'support_case_id' => $case->id,
            'user_id' => $officer->id,
            'note' => 'Sensitive internal note leak marker',
        ]);
        CaseActivity::factory()->create([
            'support_case_id' => $case->id,
            'user_id' => $officer->id,
            'action_type' => CaseActivity::ACTION_INTERNAL_NOTE_ADDED,
            'description' => 'Sensitive activity description leak marker',
            'previous_value' => 'Sensitive previous value marker',
            'new_value' => 'Sensitive new value marker',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee($case->case_reference);
        $response->assertSee('Internal note added');
        $response->assertDontSee('Sensitive dashboard subject leak marker');
        $response->assertDontSee('Sensitive dashboard description leak marker');
        $response->assertDontSee('Sensitive safe contact leak marker');
        $response->assertDontSee('Sensitive private message leak marker');
        $response->assertDontSee('Sensitive internal note leak marker');
        $response->assertDontSee('Sensitive activity description leak marker');
        $response->assertDontSee('Sensitive previous value marker');
        $response->assertDontSee('Sensitive new value marker');
    }

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}
