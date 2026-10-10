<?php

namespace Tests\Feature;

use App\Models\CaseActivity;
use App\Models\CaseMessage;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseMessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_user_can_send_message_to_assigned_officer(): void
    {
        $user = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'user_id' => $user->id,
            'status' => SupportCase::STATUS_ASSIGNED,
        ]);

        $this->actingAs($user)
            ->post(route('cases.messages.store', $case), [
                'message' => 'I can safely check messages in the afternoon.',
                'receiver_id' => User::factory()->create()->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('case_messages', [
            'support_case_id' => $case->id,
            'sender_id' => $user->id,
            'receiver_id' => $officer->id,
            'message' => 'I can safely check messages in the afternoon.',
            'read_at' => null,
        ]);
        $this->assertDatabaseHas('case_activities', [
            'support_case_id' => $case->id,
            'user_id' => $user->id,
            'action_type' => CaseActivity::ACTION_USER_RESPONDED,
        ]);
        $this->assertSame(SupportCase::STATUS_IN_PROGRESS, $case->refresh()->status);
    }

    public function test_user_cannot_message_another_users_case(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($otherUser)
            ->post(route('cases.messages.store', $case), [
                'message' => 'This should not be allowed.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('case_messages', 0);
    }

    public function test_user_cannot_send_private_message_before_case_is_assigned(): void
    {
        $user = User::factory()->create();
        $case = SupportCase::factory()->create([
            'user_id' => $user->id,
            'assigned_officer_id' => null,
        ]);

        $this->actingAs($user)
            ->post(route('cases.messages.store', $case), [
                'message' => 'Is anyone assigned yet?',
            ])
            ->assertSessionHasErrors('message');

        $this->assertDatabaseCount('case_messages', 0);
    }

    public function test_assigned_officer_can_reply_to_registered_user(): void
    {
        $user = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'user_id' => $user->id,
            'status' => SupportCase::STATUS_IN_PROGRESS,
        ]);

        $this->actingAs($officer)
            ->post(route('cases.messages.store', $case), [
                'message' => 'Thank you. I have updated your case and will wait for your reply.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('case_messages', [
            'support_case_id' => $case->id,
            'sender_id' => $officer->id,
            'receiver_id' => $user->id,
        ]);
        $this->assertDatabaseHas('case_activities', [
            'support_case_id' => $case->id,
            'user_id' => $officer->id,
            'action_type' => CaseActivity::ACTION_OFFICER_RESPONDED,
        ]);
        $this->assertSame(SupportCase::STATUS_AWAITING_USER, $case->refresh()->status);
    }

    public function test_officer_cannot_message_case_assigned_to_another_officer(): void
    {
        $user = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();
        $otherOfficer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($otherOfficer)->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($officer)
            ->post(route('cases.messages.store', $case), [
                'message' => 'This reply should not be allowed.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('case_messages', 0);
    }

    public function test_anonymous_cases_do_not_allow_private_case_messaging(): void
    {
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->anonymous()->assignedTo($officer)->create();

        $this->actingAs($officer)
            ->post(route('cases.messages.store', $case), [
                'message' => 'Anonymous cases should not use private registered messaging.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('case_messages', 0);
    }

    public function test_case_view_marks_received_user_messages_as_read(): void
    {
        $user = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'user_id' => $user->id,
        ]);
        $message = CaseMessage::factory()->create([
            'support_case_id' => $case->id,
            'sender_id' => $officer->id,
            'receiver_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('cases.show', $case))
            ->assertOk()
            ->assertSee('Conversation Thread');

        $this->assertNotNull($message->refresh()->read_at);
    }

    public function test_officer_case_view_marks_received_messages_as_read(): void
    {
        $user = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'user_id' => $user->id,
        ]);
        $message = CaseMessage::factory()->create([
            'support_case_id' => $case->id,
            'sender_id' => $user->id,
            'receiver_id' => $officer->id,
        ]);

        $this->actingAs($officer)
            ->get(route('officer.cases.show', $case))
            ->assertOk()
            ->assertSee('Conversation Thread');

        $this->assertNotNull($message->refresh()->read_at);
    }

    public function test_user_case_index_shows_unread_indicator(): void
    {
        $user = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'user_id' => $user->id,
        ]);
        CaseMessage::factory()->create([
            'support_case_id' => $case->id,
            'sender_id' => $officer->id,
            'receiver_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('cases.index'))
            ->assertOk()
            ->assertSee('1 unread');
    }
}
