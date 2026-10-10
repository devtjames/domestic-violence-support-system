<?php

namespace Tests\Feature;

use App\Models\AbuseType;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchAndFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_case_queue_searches_user_identity_filters_dates_and_sorts(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create([
            'name' => 'Filter Target User',
            'email' => 'filter-target@example.test',
        ]);
        $officer = User::factory()->supportOfficer()->create();
        $type = AbuseType::factory()->create(['name' => 'Digital Abuse']);
        $otherType = AbuseType::factory()->create(['name' => 'Financial Abuse']);

        $olderMatch = SupportCase::factory()->assignedTo($officer)->create([
            'user_id' => $user->id,
            'abuse_type_id' => $type->id,
            'subject' => 'Older matching case',
            'status' => SupportCase::STATUS_ASSIGNED,
            'urgency' => SupportCase::URGENCY_LOW,
            'created_at' => now()->subDays(8),
            'updated_at' => now()->subDays(7),
        ]);
        $newerMatch = SupportCase::factory()->assignedTo($officer)->create([
            'user_id' => $user->id,
            'abuse_type_id' => $type->id,
            'subject' => 'Newer matching case',
            'status' => SupportCase::STATUS_ASSIGNED,
            'urgency' => SupportCase::URGENCY_LOW,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDay(),
        ]);
        $outsideDate = SupportCase::factory()->create([
            'user_id' => $user->id,
            'abuse_type_id' => $type->id,
            'subject' => 'Outside date range',
            'created_at' => now()->subDays(30),
        ]);
        $otherTypeCase = SupportCase::factory()->create([
            'abuse_type_id' => $otherType->id,
            'subject' => 'Other type case',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.cases.index', [
            'search' => 'filter-target@example.test',
            'status' => SupportCase::STATUS_ASSIGNED,
            'urgency' => SupportCase::URGENCY_LOW,
            'abuse_type' => $type->id,
            'assigned_officer' => $officer->id,
            'source' => 'registered',
            'date_from' => now()->subDays(10)->toDateString(),
            'date_to' => now()->toDateString(),
            'sort' => 'oldest',
        ]));

        $response->assertOk();
        $response->assertSeeInOrder([$olderMatch->case_reference, $newerMatch->case_reference]);
        $response->assertDontSee($outsideDate->case_reference);
        $response->assertDontSee($otherTypeCase->case_reference);
        $response->assertSee('Oldest submitted');
    }

    public function test_case_queue_filters_validate_invalid_values(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.cases.index', [
                'status' => 'not-a-status',
                'urgency' => 'not-an-urgency',
                'assigned_officer' => 'not-an-officer',
                'source' => 'unknown',
                'date_from' => now()->toDateString(),
                'date_to' => now()->subDay()->toDateString(),
                'sort' => 'raw_sql',
            ]))
            ->assertSessionHasErrors(['status', 'urgency', 'assigned_officer', 'source', 'date_to', 'sort']);
    }

    public function test_officer_case_queue_filters_only_authorized_cases(): void
    {
        $officer = User::factory()->supportOfficer()->create();
        $otherOfficer = User::factory()->supportOfficer()->create();
        $type = AbuseType::factory()->create(['name' => 'Physical Abuse']);
        $otherType = AbuseType::factory()->create(['name' => 'Other']);

        $visible = SupportCase::factory()->assignedTo($officer)->create([
            'abuse_type_id' => $type->id,
            'subject' => 'Visible officer case',
            'status' => SupportCase::STATUS_ASSIGNED,
            'urgency' => SupportCase::URGENCY_CRITICAL,
            'created_at' => now()->subDays(3),
        ]);
        $hidden = SupportCase::factory()->assignedTo($otherOfficer)->create([
            'abuse_type_id' => $type->id,
            'subject' => 'Hidden officer case',
            'status' => SupportCase::STATUS_ASSIGNED,
            'urgency' => SupportCase::URGENCY_CRITICAL,
            'created_at' => now()->subDays(3),
        ]);
        $wrongType = SupportCase::factory()->assignedTo($officer)->create([
            'abuse_type_id' => $otherType->id,
            'subject' => 'Wrong type officer case',
            'status' => SupportCase::STATUS_ASSIGNED,
            'urgency' => SupportCase::URGENCY_CRITICAL,
            'created_at' => now()->subDays(3),
        ]);

        $response = $this->actingAs($officer)->get(route('officer.cases.index', [
            'search' => 'officer case',
            'status' => SupportCase::STATUS_ASSIGNED,
            'urgency' => SupportCase::URGENCY_CRITICAL,
            'abuse_type' => $type->id,
            'date_from' => now()->subDays(5)->toDateString(),
            'date_to' => now()->toDateString(),
            'sort' => 'urgency_high',
        ]));

        $response->assertOk();
        $response->assertSee($visible->case_reference);
        $response->assertDontSee($hidden->case_reference);
        $response->assertDontSee($wrongType->case_reference);
        $response->assertSee('Highest urgency');
    }

    public function test_registered_user_case_filters_preserve_ownership(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $type = AbuseType::factory()->create(['name' => 'Emotional Abuse']);
        $otherType = AbuseType::factory()->create(['name' => 'Other']);

        $match = SupportCase::factory()->create([
            'user_id' => $user->id,
            'abuse_type_id' => $type->id,
            'subject' => 'My filtered support case',
            'status' => SupportCase::STATUS_IN_PROGRESS,
            'urgency' => SupportCase::URGENCY_HIGH,
            'created_at' => now()->subDays(4),
        ]);
        $wrongType = SupportCase::factory()->create([
            'user_id' => $user->id,
            'abuse_type_id' => $otherType->id,
            'subject' => 'My wrong type case',
            'status' => SupportCase::STATUS_IN_PROGRESS,
            'urgency' => SupportCase::URGENCY_HIGH,
            'created_at' => now()->subDays(4),
        ]);
        $otherUsersCase = SupportCase::factory()->create([
            'user_id' => $otherUser->id,
            'abuse_type_id' => $type->id,
            'subject' => 'Other user filtered support case',
            'status' => SupportCase::STATUS_IN_PROGRESS,
            'urgency' => SupportCase::URGENCY_HIGH,
            'created_at' => now()->subDays(4),
        ]);

        $response = $this->actingAs($user)->get(route('cases.index', [
            'search' => 'filtered support',
            'status' => SupportCase::STATUS_IN_PROGRESS,
            'urgency' => SupportCase::URGENCY_HIGH,
            'abuse_type' => $type->id,
            'date_from' => now()->subDays(5)->toDateString(),
            'date_to' => now()->toDateString(),
            'sort' => 'recently_updated',
        ]));

        $response->assertOk();
        $response->assertSee($match->case_reference);
        $response->assertDontSee($wrongType->case_reference);
        $response->assertDontSee($otherUsersCase->case_reference);
        $response->assertSee('Recently updated');
    }
}
