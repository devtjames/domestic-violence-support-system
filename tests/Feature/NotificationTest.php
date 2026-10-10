<?php

namespace Tests\Feature;

use App\Models\AbuseType;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_case_submission_creates_neutral_user_and_admin_notifications(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $abuseType = AbuseType::factory()->create(['name' => 'Sensitive Abuse Type Marker']);

        $this->actingAs($user)->post(route('cases.store'), [
            'abuse_type_id' => $abuseType->id,
            'subject' => 'Sensitive notification subject marker',
            'description' => 'Sensitive notification description marker that should not appear in notification previews.',
            'preferred_contact_method' => SupportCase::CONTACT_PLATFORM_MESSAGE,
            'safe_contact_time' => 'Weekday afternoons',
            'safe_contact_instruction' => 'Sensitive notification contact marker',
            'urgency' => SupportCase::URGENCY_MEDIUM,
        ])->assertRedirect();

        $case = SupportCase::query()->firstOrFail();
        $userNotification = $user->notifications()->firstOrFail();
        $adminNotification = $admin->notifications()->firstOrFail();

        $this->assertSame('Your support request was received.', $userNotification->data['title']);
        $this->assertSame('New support request submitted.', $adminNotification->data['title']);
        $this->assertSame($case->case_reference, $userNotification->data['case_reference']);
        $this->assertNotificationPayloadIsNeutral($userNotification->data);
        $this->assertNotificationPayloadIsNeutral($adminNotification->data);
    }

    public function test_anonymous_critical_submission_notifies_admin_without_anonymous_private_details(): void
    {
        $admin = User::factory()->admin()->create();
        $abuseType = AbuseType::factory()->create();

        $this->post(route('anonymous.store'), [
            'anonymous_nickname' => 'Sensitive anonymous nickname marker',
            'abuse_type_id' => $abuseType->id,
            'subject' => 'Sensitive anonymous subject marker',
            'description' => 'Sensitive anonymous description marker that should stay out of notifications.',
            'preferred_contact_method' => SupportCase::CONTACT_EMAIL,
            'contact_email' => 'private-anonymous@example.test',
            'contact_phone' => '',
            'safe_contact_time' => 'Evenings',
            'safe_contact_instruction' => 'Sensitive anonymous contact marker',
            'urgency' => SupportCase::URGENCY_CRITICAL,
        ])->assertRedirect(route('anonymous.confirmation', absolute: false));

        $this->assertSame(2, $admin->notifications()->count());

        foreach ($admin->notifications as $notification) {
            $this->assertNotificationPayloadIsNeutral($notification->data);
            $payload = json_encode($notification->data);
            $this->assertStringNotContainsString('Sensitive anonymous nickname marker', $payload);
            $this->assertStringNotContainsString('private-anonymous@example.test', $payload);
        }
    }

    public function test_assignment_status_and_high_priority_notifications_are_sent_to_relevant_roles(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->create([
            'user_id' => $user->id,
            'status' => SupportCase::STATUS_SUBMITTED,
            'urgency' => SupportCase::URGENCY_LOW,
        ]);

        $this->actingAs($admin)->patch(route('admin.cases.update', $case), [
            'assigned_officer_id' => $officer->id,
            'urgency' => SupportCase::URGENCY_HIGH,
            'status' => SupportCase::STATUS_ASSIGNED,
            'resolution_summary' => '',
            'action_note' => 'Sensitive assignment action note marker',
        ])->assertRedirect(route('admin.cases.show', $case, false));

        $this->assertTrue($officer->notifications()->whereJsonContains('data->title', 'A support case was assigned to you.')->exists());
        $this->assertTrue($officer->notifications()->whereJsonContains('data->title', 'High-priority support case assigned.')->exists());
        $this->assertTrue($user->notifications()->whereJsonContains('data->title', 'Your support case assignment was updated.')->exists());
        $this->assertTrue($user->notifications()->whereJsonContains('data->title', 'Your support case status was updated.')->exists());
        $this->assertTrue($admin->notifications()->whereJsonContains('data->title', 'High-priority support request needs review.')->exists());

        foreach ($officer->notifications as $notification) {
            $this->assertNotificationPayloadIsNeutral($notification->data);
        }
    }

    public function test_private_case_reply_creates_neutral_notification_for_receiver_only(): void
    {
        $user = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'user_id' => $user->id,
            'status' => SupportCase::STATUS_IN_PROGRESS,
        ]);

        $this->actingAs($officer)->post(route('cases.messages.store', $case), [
            'message' => 'Sensitive private message body marker',
        ])->assertRedirect();

        $notification = $user->notifications()
            ->get()
            ->firstWhere('data.title', 'A support officer replied to your support case.');

        $this->assertNotNull($notification);
        $this->assertStringNotContainsString('Sensitive private message body marker', json_encode($notification->data));
        $this->assertSame(0, $officer->notifications()->count());
    }

    public function test_notification_center_marks_owned_notification_read_and_blocks_other_users_notifications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($officer)->post(route('cases.messages.store', $case), [
            'message' => 'Please check the case conversation.',
        ])->assertRedirect();

        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($otherUser)
            ->get(route('notifications.open', $notification->id))
            ->assertNotFound();

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('A support officer replied to your support case.')
            ->assertSee($case->case_reference);

        $this->actingAs($user)
            ->get(route('notifications.open', $notification->id))
            ->assertRedirect($notification->data['action_url']);

        $this->assertNotNull($notification->refresh()->read_at);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function assertNotificationPayloadIsNeutral(array $payload): void
    {
        $encoded = json_encode($payload);

        $this->assertStringNotContainsString('Sensitive notification subject marker', $encoded);
        $this->assertStringNotContainsString('Sensitive notification description marker', $encoded);
        $this->assertStringNotContainsString('Sensitive notification contact marker', $encoded);
        $this->assertStringNotContainsString('Sensitive Abuse Type Marker', $encoded);
        $this->assertStringNotContainsString('Sensitive anonymous subject marker', $encoded);
        $this->assertStringNotContainsString('Sensitive anonymous description marker', $encoded);
        $this->assertStringNotContainsString('Sensitive anonymous contact marker', $encoded);
        $this->assertStringNotContainsString('Sensitive assignment action note marker', $encoded);
    }
}
