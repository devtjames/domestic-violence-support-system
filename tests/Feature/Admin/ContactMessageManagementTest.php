<?php

namespace Tests\Feature\Admin;

use App\Models\ContactMessage;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_submit_general_contact_message_without_creating_support_case(): void
    {
        $response = $this->post(route('public.contact.store'), [
            'name' => 'General Visitor',
            'email' => 'visitor@example.test',
            'subject' => 'Platform question',
            'message' => 'I have a general question about how the platform contact page works.',
        ]);

        $response->assertRedirect(route('public.contact', absolute: false));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'General Visitor',
            'email' => 'visitor@example.test',
            'subject' => 'Platform question',
            'status' => ContactMessage::STATUS_NEW,
        ]);
        $this->assertSame(0, SupportCase::query()->count());
    }

    public function test_contact_message_validation(): void
    {
        $this->post(route('public.contact.store'), [
            'name' => '',
            'email' => 'not-email',
            'subject' => '',
            'message' => 'short',
        ])->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_admin_can_view_filter_update_and_delete_contact_messages(): void
    {
        $admin = User::factory()->admin()->create();
        $target = ContactMessage::factory()->create([
            'name' => 'Central Sender',
            'email' => 'central@example.test',
            'subject' => 'Central platform enquiry',
            'status' => ContactMessage::STATUS_NEW,
        ]);
        $other = ContactMessage::factory()->resolved()->create([
            'name' => 'Other Sender',
            'subject' => 'Resolved enquiry',
        ]);

        $index = $this->actingAs($admin)->get(route('admin.contact-messages.index', [
            'search' => 'Central',
            'status' => ContactMessage::STATUS_NEW,
        ]));

        $index->assertOk();
        $index->assertSee('Central Sender');
        $index->assertDontSee('Other Sender');

        $this->actingAs($admin)
            ->get(route('admin.contact-messages.show', $target))
            ->assertOk()
            ->assertSee('Central platform enquiry');

        $this->assertSame(ContactMessage::STATUS_READ, $target->refresh()->status);
        $this->assertNotNull($target->reviewed_at);

        $this->actingAs($admin)
            ->patch(route('admin.contact-messages.update', $target), [
                'status' => ContactMessage::STATUS_RESOLVED,
                'admin_note' => 'Handled through general enquiry follow-up.',
            ])
            ->assertRedirect(route('admin.contact-messages.show', $target, false));

        $target->refresh();
        $this->assertSame(ContactMessage::STATUS_RESOLVED, $target->status);
        $this->assertSame('Handled through general enquiry follow-up.', $target->admin_note);
        $this->assertNotNull($target->resolved_at);

        $this->actingAs($admin)
            ->delete(route('admin.contact-messages.destroy', $other))
            ->assertRedirect(route('admin.contact-messages.index', absolute: false));

        $this->assertSoftDeleted('contact_messages', ['id' => $other->id]);
    }

    public function test_contact_status_update_validation(): void
    {
        $admin = User::factory()->admin()->create();
        $message = ContactMessage::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.contact-messages.update', $message), [
                'status' => 'support_case',
                'admin_note' => str_repeat('a', 2001),
            ])
            ->assertSessionHasErrors(['status', 'admin_note']);
    }

    public function test_non_admin_cannot_access_contact_message_management(): void
    {
        $user = User::factory()->create();
        $message = ContactMessage::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.contact-messages.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.contact-messages.show', $message))
            ->assertForbidden();
    }
}
