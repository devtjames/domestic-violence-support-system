<?php

namespace Database\Seeders;

use App\Models\AbuseType;
use App\Models\AnonymousCaseAccess;
use App\Models\CaseActivity;
use App\Models\CaseMessage;
use App\Models\CaseNote;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoSupportCaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $officers = User::query()
            ->where('role', User::ROLE_SUPPORT_OFFICER)
            ->orderBy('staff_id')
            ->get()
            ->values();

        $users = User::query()
            ->where('role', User::ROLE_USER)
            ->where('account_status', User::STATUS_ACTIVE)
            ->orderBy('email')
            ->get()
            ->values();

        if ($officers->isEmpty() || $users->isEmpty()) {
            return;
        }

        foreach ($this->caseDefinitions() as $index => $definition) {
            $submittedAt = now()->subDays($definition['days_ago'])->setTime(9 + ($index % 5), 15);
            $user = $definition['is_anonymous'] ? null : $users[$index % $users->count()];
            $officer = $definition['officer_index'] === null ? null : $officers[$definition['officer_index'] % $officers->count()];
            $abuseType = $this->abuseType($definition['abuse_type']);

            $case = SupportCase::query()->firstOrCreate(
                [
                    'subject' => $definition['subject'],
                    'is_anonymous' => $definition['is_anonymous'],
                ],
                [
                    'user_id' => $user?->id,
                    'anonymous_nickname' => $definition['anonymous_nickname'],
                    'abuse_type_id' => $abuseType->id,
                    'description' => $definition['description'],
                    'preferred_contact_method' => $definition['contact_method'],
                    'contact_email' => $definition['contact_email'],
                    'contact_phone' => $definition['contact_phone'],
                    'safe_contact_time' => $definition['safe_contact_time'],
                    'safe_contact_instruction' => $definition['safe_contact_instruction'],
                    'urgency' => $definition['urgency'],
                    'status' => $definition['status'],
                    'assigned_officer_id' => $officer?->id,
                    'resolution_summary' => $definition['resolution_summary'],
                    'resolved_at' => $definition['resolved'] ? $submittedAt->copy()->addDays(5) : null,
                    'closed_at' => $definition['closed'] ? $submittedAt->copy()->addDays(8) : null,
                ]
            );

            $case->forceFill([
                'user_id' => $user?->id,
                'anonymous_nickname' => $definition['anonymous_nickname'],
                'abuse_type_id' => $abuseType->id,
                'description' => $definition['description'],
                'preferred_contact_method' => $definition['contact_method'],
                'contact_email' => $definition['contact_email'],
                'contact_phone' => $definition['contact_phone'],
                'safe_contact_time' => $definition['safe_contact_time'],
                'safe_contact_instruction' => $definition['safe_contact_instruction'],
                'urgency' => $definition['urgency'],
                'status' => $definition['status'],
                'assigned_officer_id' => $officer?->id,
                'resolution_summary' => $definition['resolution_summary'],
                'resolved_at' => $definition['resolved'] ? $submittedAt->copy()->addDays(5) : null,
                'closed_at' => $definition['closed'] ? $submittedAt->copy()->addDays(8) : null,
                'created_at' => $submittedAt,
                'updated_at' => $submittedAt->copy()->addDays($definition['updated_offset']),
            ])->save();

            if ($definition['is_anonymous']) {
                $case->anonymousAccess()->updateOrCreate(
                    ['support_case_id' => $case->id],
                    [
                        'access_code_hash' => AnonymousCaseAccess::hashAccessCode($definition['access_code']),
                        'last_accessed_at' => $submittedAt->copy()->addDays(1),
                    ]
                );
            }

            $this->seedActivities($case, $submittedAt, $officer, $definition);

            if ($user && $officer) {
                $this->seedMessages($case, $submittedAt, $user, $officer, $definition);
            }

            if ($officer) {
                $this->seedInternalNote($case, $submittedAt, $officer, $definition);
            }
        }
    }

    private function abuseType(string $name): AbuseType
    {
        return AbuseType::query()->where('name', $name)->first()
            ?? AbuseType::query()->firstOrCreate(
                ['slug' => str($name)->slug()->toString()],
                [
                    'name' => $name,
                    'description' => 'Demonstration abuse type for seed data.',
                    'status' => AbuseType::STATUS_ACTIVE,
                    'sort_order' => 99,
                ]
            );
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function seedActivities(SupportCase $case, Carbon $submittedAt, ?User $officer, array $definition): void
    {
        $this->activity($case, CaseActivity::ACTION_CASE_SUBMITTED, 'Demo support request submitted.', $case->user, null, SupportCase::STATUS_SUBMITTED, $submittedAt);

        if ($officer) {
            $this->activity($case, CaseActivity::ACTION_CASE_ASSIGNED, 'Demo case assigned to a support officer.', $officer, 'Unassigned', $officer->name, $submittedAt->copy()->addDay());
        }

        if ($definition['status'] !== SupportCase::STATUS_SUBMITTED) {
            $this->activity($case, CaseActivity::ACTION_STATUS_CHANGED, 'Demo case status updated for workflow progress.', $officer, SupportCase::STATUS_SUBMITTED, $definition['status'], $submittedAt->copy()->addDays(2));
        }

        if (in_array($definition['urgency'], [SupportCase::URGENCY_HIGH, SupportCase::URGENCY_CRITICAL], true)) {
            $this->activity($case, CaseActivity::ACTION_URGENCY_CHANGED, 'Demo case urgency reviewed by support staff.', $officer, SupportCase::URGENCY_MEDIUM, $definition['urgency'], $submittedAt->copy()->addDays(2)->addHour());
        }

        if ($definition['status'] === SupportCase::STATUS_REFERRED) {
            $this->activity($case, CaseActivity::ACTION_CASE_REFERRED, 'Demo case referred for specialist review.', $officer, null, SupportCase::STATUS_REFERRED, $submittedAt->copy()->addDays(3));
        }

        if ($definition['resolved']) {
            $this->activity($case, CaseActivity::ACTION_CASE_RESOLVED, 'Demo case resolved after support review.', $officer, null, SupportCase::STATUS_RESOLVED, $submittedAt->copy()->addDays(5));
        }

        if ($definition['closed']) {
            $this->activity($case, CaseActivity::ACTION_CASE_CLOSED, 'Demo case closed after final update.', $officer, SupportCase::STATUS_RESOLVED, SupportCase::STATUS_CLOSED, $submittedAt->copy()->addDays(8));
        }
    }

    private function activity(
        SupportCase $case,
        string $action,
        string $description,
        ?User $actor,
        ?string $previousValue,
        ?string $newValue,
        Carbon $createdAt
    ): void {
        CaseActivity::query()->updateOrCreate(
            [
                'support_case_id' => $case->id,
                'action_type' => $action,
                'description' => $description,
            ],
            [
                'user_id' => $actor?->id,
                'previous_value' => $previousValue,
                'new_value' => $newValue,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Demo Seeder',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function seedMessages(SupportCase $case, Carbon $submittedAt, User $user, User $officer, array $definition): void
    {
        $messages = [
            [
                'sender' => $officer,
                'receiver' => $user,
                'message' => 'Thank you for submitting this demo support request. I am reviewing the information and will keep replies neutral and private.',
                'read_at' => $definition['user_read_messages'] ? $submittedAt->copy()->addDays(2)->addHours(2) : null,
                'created_at' => $submittedAt->copy()->addDays(2),
            ],
            [
                'sender' => $user,
                'receiver' => $officer,
                'message' => 'I received the update and can continue by platform message for this demonstration case.',
                'read_at' => $submittedAt->copy()->addDays(3)->addHour(),
                'created_at' => $submittedAt->copy()->addDays(3),
            ],
        ];

        foreach ($messages as $message) {
            CaseMessage::query()->updateOrCreate(
                [
                    'support_case_id' => $case->id,
                    'sender_id' => $message['sender']->id,
                    'receiver_id' => $message['receiver']->id,
                    'message' => $message['message'],
                ],
                [
                    'read_at' => $message['read_at'],
                    'created_at' => $message['created_at'],
                    'updated_at' => $message['created_at'],
                ]
            );
        }

        $this->activity($case, CaseActivity::ACTION_OFFICER_RESPONDED, 'Demo support officer reply recorded.', $officer, null, null, $submittedAt->copy()->addDays(2));
        $this->activity($case, CaseActivity::ACTION_USER_RESPONDED, 'Demo registered user reply recorded.', $user, null, null, $submittedAt->copy()->addDays(3));
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function seedInternalNote(SupportCase $case, Carbon $submittedAt, User $officer, array $definition): void
    {
        $noteText = 'Demo internal note: review contact preferences and keep any follow-up language neutral.';

        CaseNote::query()->updateOrCreate(
            [
                'support_case_id' => $case->id,
                'user_id' => $officer->id,
                'note' => $noteText,
            ],
            [
                'created_at' => $submittedAt->copy()->addDays(2)->addMinutes(30),
                'updated_at' => $submittedAt->copy()->addDays(2)->addMinutes(30),
            ]
        );

        $this->activity($case, CaseActivity::ACTION_INTERNAL_NOTE_ADDED, 'Demo internal note added without exposing note content.', $officer, null, null, $submittedAt->copy()->addDays(2)->addMinutes(30));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function caseDefinitions(): array
    {
        return [
            [
                'subject' => 'Demo request about private safety planning',
                'description' => 'This is completely fictional demonstration data. The user is asking for general safety planning information and wants a private platform-message response.',
                'abuse_type' => 'Coercive Control',
                'urgency' => SupportCase::URGENCY_HIGH,
                'status' => SupportCase::STATUS_IN_PROGRESS,
                'contact_method' => SupportCase::CONTACT_PLATFORM_MESSAGE,
                'safe_contact_time' => 'Weekday mornings',
                'safe_contact_instruction' => 'Use neutral platform messages only.',
                'officer_index' => 0,
                'days_ago' => 14,
                'updated_offset' => 3,
                'is_anonymous' => false,
                'anonymous_nickname' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'resolution_summary' => null,
                'resolved' => false,
                'closed' => false,
                'access_code' => null,
                'user_read_messages' => true,
            ],
            [
                'subject' => 'Demo digital privacy concern',
                'description' => 'This fake case asks for education about account privacy, device access, and safe ways to receive support updates.',
                'abuse_type' => 'Digital Abuse',
                'urgency' => SupportCase::URGENCY_MEDIUM,
                'status' => SupportCase::STATUS_AWAITING_USER,
                'contact_method' => SupportCase::CONTACT_PLATFORM_MESSAGE,
                'safe_contact_time' => 'Evenings',
                'safe_contact_instruction' => 'Avoid email subject lines with support language.',
                'officer_index' => 1,
                'days_ago' => 10,
                'updated_offset' => 4,
                'is_anonymous' => false,
                'anonymous_nickname' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'resolution_summary' => null,
                'resolved' => false,
                'closed' => false,
                'access_code' => null,
                'user_read_messages' => false,
            ],
            [
                'subject' => 'Demo counselling referral request',
                'description' => 'This fictional request is for general counselling referral information and a calm follow-up from an assigned support officer.',
                'abuse_type' => 'Emotional Abuse',
                'urgency' => SupportCase::URGENCY_LOW,
                'status' => SupportCase::STATUS_ASSIGNED,
                'contact_method' => SupportCase::CONTACT_EMAIL,
                'safe_contact_time' => 'Afternoons',
                'safe_contact_instruction' => 'Use general wording if email is required.',
                'officer_index' => 1,
                'days_ago' => 7,
                'updated_offset' => 2,
                'is_anonymous' => false,
                'anonymous_nickname' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'resolution_summary' => null,
                'resolved' => false,
                'closed' => false,
                'access_code' => null,
                'user_read_messages' => true,
            ],
            [
                'subject' => 'Demo legal information referral',
                'description' => 'This fake support case requests general legal information resources and does not contain any real personal details.',
                'abuse_type' => 'Financial Abuse',
                'urgency' => SupportCase::URGENCY_HIGH,
                'status' => SupportCase::STATUS_REFERRED,
                'contact_method' => SupportCase::CONTACT_PLATFORM_MESSAGE,
                'safe_contact_time' => 'Weekdays only',
                'safe_contact_instruction' => 'Use platform updates first.',
                'officer_index' => 2,
                'days_ago' => 18,
                'updated_offset' => 6,
                'is_anonymous' => false,
                'anonymous_nickname' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'resolution_summary' => null,
                'resolved' => false,
                'closed' => false,
                'access_code' => null,
                'user_read_messages' => true,
            ],
            [
                'subject' => 'Demo resolved support request',
                'description' => 'This fictional case shows a completed support workflow with a neutral resolution summary for demonstration.',
                'abuse_type' => 'Psychological Abuse',
                'urgency' => SupportCase::URGENCY_MEDIUM,
                'status' => SupportCase::STATUS_RESOLVED,
                'contact_method' => SupportCase::CONTACT_PLATFORM_MESSAGE,
                'safe_contact_time' => 'Morning',
                'safe_contact_instruction' => 'No phone contact.',
                'officer_index' => 0,
                'days_ago' => 24,
                'updated_offset' => 5,
                'is_anonymous' => false,
                'anonymous_nickname' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'resolution_summary' => 'Demo resolution: provided resource information and closed the immediate review task.',
                'resolved' => true,
                'closed' => false,
                'access_code' => null,
                'user_read_messages' => true,
            ],
            [
                'subject' => 'Demo closed historical request',
                'description' => 'This completely fictional case is included so reports and dashboards have a closed support workflow example.',
                'abuse_type' => 'Verbal Abuse',
                'urgency' => SupportCase::URGENCY_LOW,
                'status' => SupportCase::STATUS_CLOSED,
                'contact_method' => SupportCase::CONTACT_PLATFORM_MESSAGE,
                'safe_contact_time' => 'Not needed',
                'safe_contact_instruction' => 'Case closed after demonstration review.',
                'officer_index' => 2,
                'days_ago' => 32,
                'updated_offset' => 8,
                'is_anonymous' => false,
                'anonymous_nickname' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'resolution_summary' => 'Demo closure: support workflow completed for sample data.',
                'resolved' => true,
                'closed' => true,
                'access_code' => null,
                'user_read_messages' => true,
            ],
            [
                'subject' => 'Demo newly submitted request',
                'description' => 'This fake case remains newly submitted so the admin queue has an unassigned request to review.',
                'abuse_type' => 'Other',
                'urgency' => SupportCase::URGENCY_MEDIUM,
                'status' => SupportCase::STATUS_SUBMITTED,
                'contact_method' => SupportCase::CONTACT_PLATFORM_MESSAGE,
                'safe_contact_time' => 'Weekdays',
                'safe_contact_instruction' => 'Platform messages are preferred.',
                'officer_index' => null,
                'days_ago' => 1,
                'updated_offset' => 0,
                'is_anonymous' => false,
                'anonymous_nickname' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'resolution_summary' => null,
                'resolved' => false,
                'closed' => false,
                'access_code' => null,
                'user_read_messages' => true,
            ],
            [
                'subject' => 'Demo child-related planning request',
                'description' => 'This fictional case asks for general child-related safety planning resources without any real child or family details.',
                'abuse_type' => 'Child-related Abuse',
                'urgency' => SupportCase::URGENCY_HIGH,
                'status' => SupportCase::STATUS_IN_PROGRESS,
                'contact_method' => SupportCase::CONTACT_PLATFORM_MESSAGE,
                'safe_contact_time' => 'School hours',
                'safe_contact_instruction' => 'Use careful neutral wording.',
                'officer_index' => 0,
                'days_ago' => 5,
                'updated_offset' => 1,
                'is_anonymous' => false,
                'anonymous_nickname' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'resolution_summary' => null,
                'resolved' => false,
                'closed' => false,
                'access_code' => null,
                'user_read_messages' => false,
            ],
            [
                'subject' => 'Anonymous demo critical request',
                'description' => 'This anonymous demo record is fictional and exists to show high-priority anonymous intake without collecting unnecessary identity information.',
                'abuse_type' => 'Physical Abuse',
                'urgency' => SupportCase::URGENCY_CRITICAL,
                'status' => SupportCase::STATUS_UNDER_REVIEW,
                'contact_method' => SupportCase::CONTACT_NO_DIRECT_CONTACT,
                'safe_contact_time' => null,
                'safe_contact_instruction' => 'No direct contact requested.',
                'officer_index' => null,
                'days_ago' => 2,
                'updated_offset' => 1,
                'is_anonymous' => true,
                'anonymous_nickname' => 'River',
                'contact_email' => null,
                'contact_phone' => null,
                'resolution_summary' => null,
                'resolved' => false,
                'closed' => false,
                'access_code' => 'DEMOA123',
                'user_read_messages' => true,
            ],
            [
                'subject' => 'Anonymous demo information request',
                'description' => 'This fake anonymous record requests general information and demonstrates secure tracking with a hashed access code.',
                'abuse_type' => 'Sexual Abuse',
                'urgency' => SupportCase::URGENCY_MEDIUM,
                'status' => SupportCase::STATUS_ASSIGNED,
                'contact_method' => SupportCase::CONTACT_EMAIL,
                'safe_contact_time' => null,
                'safe_contact_instruction' => 'Use neutral language if a response is needed.',
                'officer_index' => 1,
                'days_ago' => 12,
                'updated_offset' => 3,
                'is_anonymous' => true,
                'anonymous_nickname' => 'Blue',
                'contact_email' => 'anonymous.demo@example.test',
                'contact_phone' => null,
                'resolution_summary' => null,
                'resolved' => false,
                'closed' => false,
                'access_code' => 'DEMOA456',
                'user_read_messages' => true,
            ],
            [
                'subject' => 'Anonymous demo resolved request',
                'description' => 'This fictional anonymous case demonstrates a resolved anonymous workflow without exposing internal notes to tracking visitors.',
                'abuse_type' => 'Coercive Control',
                'urgency' => SupportCase::URGENCY_LOW,
                'status' => SupportCase::STATUS_RESOLVED,
                'contact_method' => SupportCase::CONTACT_NO_DIRECT_CONTACT,
                'safe_contact_time' => null,
                'safe_contact_instruction' => 'No direct contact requested.',
                'officer_index' => 2,
                'days_ago' => 20,
                'updated_offset' => 5,
                'is_anonymous' => true,
                'anonymous_nickname' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'resolution_summary' => 'Demo anonymous case marked resolved after staff review.',
                'resolved' => true,
                'closed' => false,
                'access_code' => 'DEMOA789',
                'user_read_messages' => true,
            ],
        ];
    }
}
