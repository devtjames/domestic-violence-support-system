<?php

namespace App\Services;

use App\Models\SupportCase;
use App\Models\User;
use App\Notifications\CaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class CaseNotificationService
{
    public function caseSubmitted(SupportCase $supportCase): void
    {
        if ($supportCase->user?->isActive()) {
            $supportCase->user->notify($this->caseNotification(
                $supportCase,
                'case_submitted',
                'Your support request was received.',
                'A support request has been created and is ready for review.',
                $supportCase->user
            ));
        }

        $this->notifyAdmins($this->caseNotification(
            $supportCase,
            'admin_case_submitted',
            'New support request submitted.',
            'A support request is ready for review.',
            null,
            'admin'
        ));

        if ($this->isHighPriority($supportCase)) {
            $this->highPriorityCase($supportCase);
        }
    }

    public function assignmentChanged(SupportCase $supportCase, mixed $previousOfficerId = null): void
    {
        $assignedOfficer = $supportCase->assignedOfficer;

        if ($assignedOfficer?->isActive()) {
            $assignedOfficer->notify($this->caseNotification(
                $supportCase,
                $previousOfficerId ? 'case_reassigned' : 'case_assigned',
                $previousOfficerId ? 'A support case was reassigned to you.' : 'A support case was assigned to you.',
                'Open the case queue to review the latest assignment.',
                $assignedOfficer
            ));

            if ($this->isHighPriority($supportCase)) {
                $assignedOfficer->notify($this->caseNotification(
                    $supportCase,
                    'high_priority_case',
                    'High-priority support case assigned.',
                    'A high-priority case is assigned to you for review.',
                    $assignedOfficer
                ));
            }
        }

        if ($previousOfficerId && (int) $previousOfficerId !== (int) ($supportCase->assigned_officer_id ?? 0)) {
            $previousOfficer = User::query()->find($previousOfficerId);

            if ($previousOfficer?->isActive()) {
                $previousOfficer->notify($this->caseNotification(
                    $supportCase,
                    'case_reassigned',
                    'A support case assignment changed.',
                    'A case is no longer assigned to your queue.',
                    $previousOfficer
                ));
            }
        }

        if ($supportCase->user?->isActive()) {
            $supportCase->user->notify($this->caseNotification(
                $supportCase,
                'case_assigned',
                'Your support case assignment was updated.',
                'A support officer assignment has been updated for your case.',
                $supportCase->user
            ));
        }
    }

    public function officerAcceptedCase(SupportCase $supportCase, User $officer): void
    {
        if ($supportCase->user?->isActive()) {
            $supportCase->user->notify($this->caseNotification(
                $supportCase,
                'case_assigned',
                'Your support case assignment was updated.',
                'A support officer assignment has been updated for your case.',
                $supportCase->user
            ));
        }

        $this->notifyAdmins($this->caseNotification(
            $supportCase,
            'officer_activity',
            'Support officer accepted a case.',
            'A support officer accepted a case from the open queue.',
            null,
            'admin'
        ));

        if ($this->isHighPriority($supportCase)) {
            $officer->notify($this->caseNotification(
                $supportCase,
                'high_priority_case',
                'High-priority support case assigned.',
                'A high-priority case is assigned to you for review.',
                $officer
            ));
        }
    }

    public function statusChanged(SupportCase $supportCase, string $previousStatus, ?User $actor = null): void
    {
        if ($supportCase->user?->isActive() && $actor?->id !== $supportCase->user_id) {
            $title = match ($supportCase->status) {
                SupportCase::STATUS_RESOLVED => 'Your support case has been marked resolved.',
                SupportCase::STATUS_CLOSED => 'Your support case has been closed.',
                default => 'Your support case status was updated.',
            };

            $supportCase->user->notify($this->caseNotification(
                $supportCase,
                'status_changed',
                $title,
                'Open your case to view the latest workflow status.',
                $supportCase->user
            ));
        }

        if ($supportCase->assignedOfficer?->isActive() && $actor?->id !== $supportCase->assigned_officer_id) {
            $supportCase->assignedOfficer->notify($this->caseNotification(
                $supportCase,
                'status_changed',
                'A support case status was updated.',
                'An assigned case has a workflow status update.',
                $supportCase->assignedOfficer
            ));
        }

        if ($actor?->isSupportOfficer()) {
            $this->notifyAdmins($this->caseNotification(
                $supportCase,
                'officer_activity',
                'Support officer updated a case.',
                'A support officer recorded a workflow update.',
                null,
                'admin'
            ));
        }
    }

    public function urgencyChanged(SupportCase $supportCase, string $previousUrgency): void
    {
        if (! $this->isHighPriority($supportCase) || $previousUrgency === $supportCase->urgency) {
            return;
        }

        $this->highPriorityCase($supportCase);
    }

    public function privateMessageSent(SupportCase $supportCase, User $sender, User $receiver): void
    {
        $title = $sender->isSupportOfficer()
            ? 'A support officer replied to your support case.'
            : 'A registered user replied to a support case.';

        $body = $sender->isSupportOfficer()
            ? 'Open your case conversation to view the reply.'
            : 'Open the assigned case conversation to view the reply.';

        $receiver->notify($this->caseNotification(
            $supportCase,
            'case_reply',
            $title,
            $body,
            $receiver
        ));
    }

    private function highPriorityCase(SupportCase $supportCase): void
    {
        $this->notifyAdmins($this->caseNotification(
            $supportCase,
            'high_priority_case',
            'High-priority support request needs review.',
            'A high-priority case is available in the case queue.',
            null,
            'admin'
        ));

        if ($supportCase->assignedOfficer?->isActive()) {
            $supportCase->assignedOfficer->notify($this->caseNotification(
                $supportCase,
                'high_priority_case',
                'High-priority support case assigned.',
                'A high-priority case is assigned to you for review.',
                $supportCase->assignedOfficer
            ));
        }
    }

    private function caseNotification(
        SupportCase $supportCase,
        string $category,
        string $title,
        string $body,
        ?User $recipient = null,
        ?string $recipientRole = null
    ): CaseNotification {
        return new CaseNotification([
            'category' => $category,
            'title' => $title,
            'body' => $body,
            'case_reference' => $supportCase->case_reference,
            'action_url' => $this->caseUrl($supportCase, $recipient, $recipientRole),
        ]);
    }

    private function caseUrl(SupportCase $supportCase, ?User $recipient = null, ?string $recipientRole = null): string
    {
        $role = $recipientRole ?? $recipient?->role;

        return match ($role) {
            User::ROLE_ADMIN => route('admin.cases.show', $supportCase),
            User::ROLE_SUPPORT_OFFICER => route('officer.cases.show', $supportCase),
            User::ROLE_USER => route('cases.show', $supportCase),
            default => route('admin.cases.show', $supportCase),
        };
    }

    private function notifyAdmins(CaseNotification $notification): void
    {
        Notification::send($this->activeAdmins(), $notification);
    }

    /**
     * @return Collection<int, User>
     */
    private function activeAdmins(): Collection
    {
        return User::query()
            ->where('role', User::ROLE_ADMIN)
            ->where('account_status', User::STATUS_ACTIVE)
            ->get();
    }

    private function isHighPriority(SupportCase $supportCase): bool
    {
        return in_array($supportCase->urgency, [SupportCase::URGENCY_HIGH, SupportCase::URGENCY_CRITICAL], true);
    }
}
