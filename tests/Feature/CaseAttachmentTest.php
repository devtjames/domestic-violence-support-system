<?php

namespace Tests\Feature;

use App\Models\AbuseType;
use App\Models\CaseActivity;
use App\Models\CaseAttachment;
use App\Models\SupportCase;
use App\Models\User;
use App\Services\CaseAttachmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CaseAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_user_can_submit_case_with_private_attachment(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $abuseType = AbuseType::factory()->create();

        $this->actingAs($user)
            ->post(route('cases.store'), $this->casePayload($abuseType, [
                'attachments' => [
                    UploadedFile::fake()->create('safety-plan.pdf', 120, 'application/pdf'),
                ],
            ]))
            ->assertRedirect();

        $case = SupportCase::query()->firstOrFail();
        $attachment = CaseAttachment::query()->firstOrFail();

        $this->assertSame($case->id, $attachment->support_case_id);
        $this->assertSame($user->id, $attachment->uploaded_by_user_id);
        $this->assertSame(CaseAttachment::SOURCE_REGISTERED_USER, $attachment->upload_source);
        $this->assertSame('safety-plan.pdf', $attachment->original_name);
        $this->assertNotSame('safety-plan.pdf', $attachment->stored_name);
        $this->assertStringStartsWith('case-attachments/'.$case->case_reference.'/', $attachment->path);
        Storage::disk('local')->assertExists($attachment->path);

        $this->assertDatabaseHas('case_activities', [
            'support_case_id' => $case->id,
            'action_type' => CaseActivity::ACTION_ATTACHMENT_ADDED,
        ]);

        $this->actingAs($user)
            ->get(route('cases.show', $case))
            ->assertOk()
            ->assertSee('Attachments')
            ->assertSee('safety-plan.pdf');
    }

    public function test_attachment_upload_validation_limits_file_types_size_and_count(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $abuseType = AbuseType::factory()->create();

        $this->actingAs($user)
            ->post(route('cases.store'), $this->casePayload($abuseType, [
                'attachments' => [
                    UploadedFile::fake()->create('unsafe.exe', 100, 'application/x-msdownload'),
                ],
            ]))
            ->assertSessionHasErrors('attachments.0');

        $this->actingAs($user)
            ->post(route('cases.store'), $this->casePayload($abuseType, [
                'attachments' => [
                    UploadedFile::fake()->create('large.pdf', CaseAttachment::MAX_UPLOAD_KB + 1, 'application/pdf'),
                ],
            ]))
            ->assertSessionHasErrors('attachments.0');

        $this->actingAs($user)
            ->post(route('cases.store'), $this->casePayload($abuseType, [
                'attachments' => [
                    UploadedFile::fake()->create('one.pdf', 100, 'application/pdf'),
                    UploadedFile::fake()->create('two.pdf', 100, 'application/pdf'),
                    UploadedFile::fake()->create('three.pdf', 100, 'application/pdf'),
                    UploadedFile::fake()->create('four.pdf', 100, 'application/pdf'),
                ],
            ]))
            ->assertSessionHasErrors('attachments');
    }

    public function test_only_authorized_users_can_upload_and_download_case_attachments(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();
        $otherOfficer = User::factory()->supportOfficer()->create();
        $admin = User::factory()->admin()->create();
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'user_id' => $owner->id,
        ]);

        $attachment = app(CaseAttachmentService::class)->store(
            $case,
            UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg'),
            $owner
        );

        $this->actingAs($owner)
            ->get(route('case-attachments.download', $attachment))
            ->assertOk()
            ->assertHeader('content-disposition');

        $this->actingAs($officer)
            ->get(route('case-attachments.download', $attachment))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('case-attachments.download', $attachment))
            ->assertOk();

        $this->actingAs($otherUser)
            ->get(route('case-attachments.download', $attachment))
            ->assertForbidden();

        $this->actingAs($otherOfficer)
            ->get(route('case-attachments.download', $attachment))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->post(route('cases.attachments.store', $case), [
                'attachments' => [
                    UploadedFile::fake()->create('other.pdf', 100, 'application/pdf'),
                ],
            ])
            ->assertForbidden();

        $this->actingAs($officer)
            ->post(route('cases.attachments.store', $case), [
                'attachments' => [
                    UploadedFile::fake()->create('officer-note.pdf', 100, 'application/pdf'),
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('case_attachments', [
            'support_case_id' => $case->id,
            'original_name' => 'officer-note.pdf',
            'upload_source' => CaseAttachment::SOURCE_SUPPORT_OFFICER,
        ]);
    }

    public function test_anonymous_submission_can_store_attachment_without_exposing_it_on_tracking_page(): void
    {
        Storage::fake('local');

        $abuseType = AbuseType::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->post(route('anonymous.store'), [
            'anonymous_nickname' => 'Visitor',
            'abuse_type_id' => $abuseType->id,
            'subject' => 'Anonymous attachment request',
            'description' => 'This anonymous request includes enough detail for support review.',
            'preferred_contact_method' => SupportCase::CONTACT_NO_DIRECT_CONTACT,
            'safe_contact_instruction' => 'Do not call.',
            'urgency' => SupportCase::URGENCY_LOW,
            'attachments' => [
                UploadedFile::fake()->create('private-context.pdf', 100, 'application/pdf'),
            ],
        ])->assertRedirect(route('anonymous.confirmation', absolute: false));

        $case = SupportCase::query()->where('is_anonymous', true)->firstOrFail();
        $attachment = CaseAttachment::query()->firstOrFail();

        $this->assertSame(CaseAttachment::SOURCE_ANONYMOUS, $attachment->upload_source);
        Storage::disk('local')->assertExists($attachment->path);

        $accessCode = session('anonymous_access_code');

        $this->post(route('anonymous.track.lookup'), [
            'case_reference' => $case->case_reference,
            'access_code' => $accessCode,
        ])
            ->assertOk()
            ->assertDontSee('private-context.pdf')
            ->assertDontSee(route('case-attachments.download', $attachment, false));

        $this->actingAs($admin)
            ->get(route('admin.cases.show', $case))
            ->assertOk()
            ->assertSee('private-context.pdf');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function casePayload(AbuseType $abuseType, array $overrides = []): array
    {
        return array_merge([
            'abuse_type_id' => $abuseType->id,
            'subject' => 'Attachment support request',
            'description' => 'This support request includes enough detail for the validation rules to accept it.',
            'preferred_contact_method' => SupportCase::CONTACT_PLATFORM_MESSAGE,
            'safe_contact_time' => 'Morning',
            'safe_contact_instruction' => 'Use platform messages.',
            'urgency' => SupportCase::URGENCY_LOW,
        ], $overrides);
    }
}
