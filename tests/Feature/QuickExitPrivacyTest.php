<?php

namespace Tests\Feature;

use App\Models\AnonymousCaseAccess;
use App\Models\CaseMessage;
use App\Models\CaseNote;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickExitPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_quick_exit_redirects_to_configured_neutral_destination(): void
    {
        config(['support.quick_exit_url' => 'https://example.org/neutral']);

        $response = $this->get(route('public.quick-exit'));

        $response->assertRedirect('https://example.org/neutral');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control', ''));
        $this->assertStringContainsString('no-cache', $response->headers->get('Cache-Control', ''));
    }

    public function test_public_support_entry_pages_show_browser_history_warning_and_noindex(): void
    {
        $this->get(route('public.get-support'))
            ->assertOk()
            ->assertSee('Quick Exit does not erase browser history')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $this->get(route('anonymous.create'))
            ->assertOk()
            ->assertSee('Quick Exit does not erase browser history')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_authenticated_support_pages_use_neutral_titles_and_privacy_warning(): void
    {
        $user = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'user_id' => $user->id,
            'subject' => 'Sensitive subject that should not be in page title',
        ]);

        $response = $this->actingAs($user)->get(route('cases.show', $case));

        $response
            ->assertOk()
            ->assertSee('Quick Exit does not erase browser history')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee('<title>'.config('app.name', 'Domestic Support').'</title>', false)
            ->assertDontSee('<title>Sensitive subject that should not be in page title', false);
    }

    public function test_registered_and_anonymous_views_do_not_show_staff_private_records_or_previews(): void
    {
        $user = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'user_id' => $user->id,
        ]);
        CaseNote::factory()->create([
            'support_case_id' => $case->id,
            'user_id' => $officer->id,
            'note' => 'Private staff-only note preview.',
        ]);
        CaseMessage::factory()->create([
            'support_case_id' => $case->id,
            'sender_id' => $user->id,
            'receiver_id' => $officer->id,
            'message' => 'Message intended for assigned officer only.',
        ]);

        $this->actingAs($user)
            ->get(route('cases.show', $case))
            ->assertOk()
            ->assertDontSee('Private staff-only note preview.');

        $anonymousCase = SupportCase::factory()->anonymous()->create();
        $anonymousCase->anonymousAccess()->create([
            'access_code_hash' => AnonymousCaseAccess::hashAccessCode('ABCDEFGH'),
        ]);
        CaseNote::factory()->create([
            'support_case_id' => $anonymousCase->id,
            'note' => 'Anonymous staff-only note preview.',
        ]);

        $this->post(route('anonymous.track.lookup'), [
            'case_reference' => $anonymousCase->case_reference,
            'access_code' => 'ABCDEFGH',
        ])
            ->assertOk()
            ->assertDontSee('Anonymous staff-only note preview.')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_configured_notification_preview_is_sensitive_data_safe(): void
    {
        $preview = config('support.privacy_safe_notification_preview');

        $this->assertSame('Your support case has been updated.', $preview);
        $this->assertStringNotContainsString('abuse', strtolower($preview));
        $this->assertStringNotContainsString('description', strtolower($preview));
    }
}
