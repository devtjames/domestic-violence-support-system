<?php

namespace Tests\Feature;

use App\Models\AbuseType;
use App\Models\AnonymousCaseAccess;
use App\Models\SupportCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnonymousSupportCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_submit_anonymous_support_request(): void
    {
        $abuseType = AbuseType::factory()->create();

        $response = $this->post(route('anonymous.store'), $this->validData([
            'abuse_type_id' => $abuseType->id,
        ]));

        $case = SupportCase::query()->firstOrFail();
        $access = AnonymousCaseAccess::query()->firstOrFail();

        $response
            ->assertRedirect(route('anonymous.confirmation', absolute: false))
            ->assertSessionHas('anonymous_case_reference', $case->case_reference)
            ->assertSessionHas('anonymous_access_code');

        $this->assertTrue($case->is_anonymous);
        $this->assertNull($case->user_id);
        $this->assertSame(SupportCase::STATUS_SUBMITTED, $case->status);
        $this->assertSame($case->id, $access->support_case_id);
        $this->assertMatchesRegularExpression('/^DV-\d{4}-\d{6}$/', $case->case_reference);
    }

    public function test_anonymous_access_code_is_hashed_not_stored_plain_text(): void
    {
        $abuseType = AbuseType::factory()->create();

        $response = $this->post(route('anonymous.store'), $this->validData([
            'abuse_type_id' => $abuseType->id,
        ]));

        $accessCode = session('anonymous_access_code');
        $access = AnonymousCaseAccess::query()->firstOrFail();

        $response->assertSessionHas('anonymous_access_code');
        $this->assertNotSame($accessCode, $access->access_code_hash);
        $this->assertTrue($access->matches($accessCode));
    }

    public function test_confirmation_requires_flashed_credentials(): void
    {
        $this->get(route('anonymous.confirmation'))
            ->assertRedirect(route('anonymous.create', absolute: false));
    }

    public function test_valid_anonymous_credentials_show_limited_tracking_information(): void
    {
        $abuseType = AbuseType::factory()->create();

        $this->post(route('anonymous.store'), $this->validData([
            'abuse_type_id' => $abuseType->id,
            'description' => 'This sensitive description should not appear on the public tracking result page.',
            'contact_email' => 'private@example.test',
            'contact_phone' => '555-0199',
        ]));

        $case = SupportCase::query()->firstOrFail();
        $accessCode = session('anonymous_access_code');

        $response = $this->post(route('anonymous.track.lookup'), [
            'case_reference' => $case->case_reference,
            'access_code' => strtolower($accessCode),
        ]);

        $response
            ->assertOk()
            ->assertSee($case->case_reference)
            ->assertSee('Submitted')
            ->assertSee('Public Responses')
            ->assertDontSee('This sensitive description')
            ->assertDontSee('private@example.test')
            ->assertDontSee('555-0199');

        $this->assertNotNull($case->anonymousAccess->refresh()->last_accessed_at);
    }

    public function test_invalid_anonymous_credentials_are_rejected(): void
    {
        $case = SupportCase::factory()->create([
            'is_anonymous' => true,
            'user_id' => null,
        ]);

        $case->anonymousAccess()->create([
            'access_code_hash' => AnonymousCaseAccess::hashAccessCode('ABCDEFGH'),
        ]);

        $this->post(route('anonymous.track.lookup'), [
            'case_reference' => $case->case_reference,
            'access_code' => 'BADCODE1',
        ])->assertSessionHasErrors('case_reference');
    }

    public function test_registered_case_cannot_be_tracked_as_anonymous_case(): void
    {
        $case = SupportCase::factory()->create([
            'is_anonymous' => false,
        ]);

        $case->anonymousAccess()->create([
            'access_code_hash' => AnonymousCaseAccess::hashAccessCode('ABCDEFGH'),
        ]);

        $this->post(route('anonymous.track.lookup'), [
            'case_reference' => $case->case_reference,
            'access_code' => 'ABCDEFGH',
        ])->assertSessionHasErrors('case_reference');
    }

    public function test_anonymous_lookup_attempts_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this
                ->withServerVariables(['REMOTE_ADDR' => '203.0.113.50'])
                ->post(route('anonymous.track.lookup'), [
                    'case_reference' => 'DV-2026-999999',
                    'access_code' => 'BADCODE1',
                ]);
        }

        $this
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.50'])
            ->post(route('anonymous.track.lookup'), [
                'case_reference' => 'DV-2026-999999',
                'access_code' => 'BADCODE1',
            ])
            ->assertStatus(429);
    }

    public function test_anonymous_support_request_validation(): void
    {
        $inactiveAbuseType = AbuseType::factory()->inactive()->create();

        $this->post(route('anonymous.store'), [
            'anonymous_nickname' => str_repeat('a', 81),
            'abuse_type_id' => $inactiveAbuseType->id,
            'subject' => 'Bad',
            'description' => 'Too short',
            'preferred_contact_method' => 'invalid',
            'contact_email' => 'not-email',
            'contact_phone' => 'call me',
            'safe_contact_instruction' => str_repeat('b', 1001),
            'urgency' => 'urgent',
        ])->assertSessionHasErrors([
            'anonymous_nickname',
            'abuse_type_id',
            'subject',
            'description',
            'preferred_contact_method',
            'contact_email',
            'contact_phone',
            'safe_contact_instruction',
            'urgency',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'anonymous_nickname' => 'Helper',
            'abuse_type_id' => AbuseType::factory()->create()->id,
            'subject' => 'Need private support',
            'description' => 'I need private support and would like this request reviewed without creating an account.',
            'preferred_contact_method' => SupportCase::CONTACT_NO_DIRECT_CONTACT,
            'contact_email' => '',
            'contact_phone' => '',
            'safe_contact_instruction' => 'Use this anonymous tracking page for updates.',
            'urgency' => SupportCase::URGENCY_LOW,
        ], $overrides);
    }
}
