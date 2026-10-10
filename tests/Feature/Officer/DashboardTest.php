<?php

namespace Tests\Feature\Officer;

use App\Models\AbuseType;
use App\Models\CaseMessage;
use App\Models\CaseNote;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_dashboard_shows_only_authorized_case_statistics_and_lists(): void
    {
        $officer = User::factory()->supportOfficer()->create(['name' => 'Assigned Officer']);
        $otherOfficer = User::factory()->supportOfficer()->create(['name' => 'Other Officer']);
        $registeredUser = User::factory()->create();
        $abuseType = AbuseType::factory()->create(['name' => 'Digital Abuse']);

        $assignedOpenCase = SupportCase::factory()->create([
            'user_id' => $registeredUser->id,
            'assigned_officer_id' => $officer->id,
            'abuse_type_id' => $abuseType->id,
            'status' => SupportCase::STATUS_IN_PROGRESS,
            'urgency' => SupportCase::URGENCY_HIGH,
            'subject' => 'Assigned sensitive subject marker',
            'description' => 'Assigned sensitive description marker',
            'safe_contact_instruction' => 'Assigned sensitive safe contact marker',
        ]);
        $assignedResolvedCase = SupportCase::factory()->create([
            'user_id' => $registeredUser->id,
            'assigned_officer_id' => $officer->id,
            'abuse_type_id' => $abuseType->id,
            'status' => SupportCase::STATUS_RESOLVED,
            'urgency' => SupportCase::URGENCY_MEDIUM,
        ]);
        $openQueueCase = SupportCase::factory()->create([
            'abuse_type_id' => $abuseType->id,
            'assigned_officer_id' => null,
            'status' => SupportCase::STATUS_SUBMITTED,
            'urgency' => SupportCase::URGENCY_CRITICAL,
        ]);
        $otherOfficerCase = SupportCase::factory()->create([
            'assigned_officer_id' => $otherOfficer->id,
            'status' => SupportCase::STATUS_IN_PROGRESS,
            'subject' => 'Other officer sensitive subject marker',
        ]);
        $closedQueueCase = SupportCase::factory()->create([
            'assigned_officer_id' => null,
            'status' => SupportCase::STATUS_CLOSED,
        ]);

        CaseMessage::factory()->create([
            'support_case_id' => $assignedOpenCase->id,
            'sender_id' => $registeredUser->id,
            'receiver_id' => $officer->id,
            'message' => 'Assigned sensitive message marker',
            'read_at' => null,
        ]);
        CaseMessage::factory()->create([
            'support_case_id' => $otherOfficerCase->id,
            'sender_id' => $registeredUser->id,
            'receiver_id' => $otherOfficer->id,
            'read_at' => null,
        ]);
        CaseNote::factory()->create([
            'support_case_id' => $assignedOpenCase->id,
            'user_id' => $officer->id,
            'note' => 'Assigned sensitive internal note marker',
        ]);

        $response = $this->actingAs($officer)->get(route('officer.dashboard'));

        $response->assertOk();
        $response->assertSee('Officer Dashboard');
        $response->assertSee($assignedOpenCase->case_reference);
        $response->assertSee($assignedResolvedCase->case_reference);
        $response->assertSee($openQueueCase->case_reference);
        $response->assertDontSee($otherOfficerCase->case_reference);
        $response->assertDontSee($closedQueueCase->case_reference);
        $response->assertViewHas('summaryCards', function (array $summaryCards): bool {
            $cards = collect($summaryCards)->pluck('value', 'label');

            return $cards['Assigned Cases'] === 2
                && $cards['Open Assigned Cases'] === 1
                && $cards['Open Case Queue'] === 1
                && $cards['High-Priority Cases'] === 2
                && $cards['Awaiting Response'] === 1
                && $cards['Unread Messages'] === 1;
        });
        $response->assertDontSee('Assigned sensitive subject marker');
        $response->assertDontSee('Assigned sensitive description marker');
        $response->assertDontSee('Assigned sensitive safe contact marker');
        $response->assertDontSee('Assigned sensitive message marker');
        $response->assertDontSee('Assigned sensitive internal note marker');
        $response->assertDontSee('Other officer sensitive subject marker');
    }

    public function test_non_officer_cannot_access_officer_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('officer.dashboard'))
            ->assertForbidden();
    }
}
