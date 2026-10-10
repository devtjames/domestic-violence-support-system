<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaseMessageRequest;
use App\Models\AuditLog;
use App\Models\CaseActivity;
use App\Models\SupportCase;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\CaseNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CaseMessageController extends Controller
{
    public function store(StoreCaseMessageRequest $request, SupportCase $supportCase): RedirectResponse
    {
        $sender = $request->user();
        $receiver = $this->receiverFor($supportCase, $sender);

        if (! $receiver instanceof User || ! $receiver->isActive()) {
            throw ValidationException::withMessages([
                'message' => 'A support officer must be assigned before private messages can be sent.',
            ]);
        }

        if ($supportCase->status === SupportCase::STATUS_CLOSED) {
            throw ValidationException::withMessages([
                'message' => 'Closed cases cannot receive new private messages.',
            ]);
        }

        DB::transaction(function () use ($request, $supportCase, $sender, $receiver) {
            $supportCase->messages()->create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'message' => $request->validated('message'),
            ]);

            $this->recordMessageActivity($supportCase, $sender, $request);
            $this->advanceCaseStatus($supportCase, $sender, $request);

            app(CaseNotificationService::class)->privateMessageSent($supportCase->refresh(), $sender, $receiver);

            app(AuditLogger::class)->record(
                $request,
                AuditLog::MODULE_CASES,
                $sender->isSupportOfficer() ? 'officer_reply_sent' : 'user_reply_received',
                $sender->isSupportOfficer()
                    ? 'Support officer sent a private case reply for '.$supportCase->case_reference.'.'
                    : 'Registered user sent a private case reply for '.$supportCase->case_reference.'.',
                $supportCase,
                ['sender_role' => $sender->role, 'receiver_role' => $receiver->role]
            );
        });

        return back()->with('status', 'Private message sent.');
    }

    private function receiverFor(SupportCase $supportCase, User $sender): ?User
    {
        if ($sender->isRegisteredUser()) {
            return $supportCase->assignedOfficer;
        }

        if ($sender->isSupportOfficer() && $supportCase->assigned_officer_id === $sender->id) {
            return $supportCase->user;
        }

        return null;
    }

    private function recordMessageActivity(SupportCase $supportCase, User $sender, StoreCaseMessageRequest $request): void
    {
        $supportCase->recordActivity(
            $sender->isSupportOfficer() ? CaseActivity::ACTION_OFFICER_RESPONDED : CaseActivity::ACTION_USER_RESPONDED,
            $sender->isSupportOfficer() ? 'Support officer sent a private case message.' : 'Registered user sent a private case message.',
            $sender,
            null,
            null,
            $request
        );
    }

    private function advanceCaseStatus(SupportCase $supportCase, User $sender, StoreCaseMessageRequest $request): void
    {
        $nextStatus = null;

        if ($sender->isSupportOfficer() && ! in_array($supportCase->status, [SupportCase::STATUS_RESOLVED, SupportCase::STATUS_CLOSED], true)) {
            $nextStatus = SupportCase::STATUS_AWAITING_USER;
        }

        if ($sender->isRegisteredUser() && in_array($supportCase->status, [SupportCase::STATUS_ASSIGNED, SupportCase::STATUS_AWAITING_USER], true)) {
            $nextStatus = SupportCase::STATUS_IN_PROGRESS;
        }

        if ($nextStatus === null || $nextStatus === $supportCase->status) {
            return;
        }

        $previousStatus = $supportCase->status;

        $supportCase->update(['status' => $nextStatus]);
        $supportCase->recordActivity(
            CaseActivity::ACTION_STATUS_CHANGED,
            'Case status changed after a private message.',
            $sender,
            $previousStatus,
            $nextStatus,
            $request
        );

        app(CaseNotificationService::class)->statusChanged($supportCase->refresh(), $previousStatus, $sender);
    }
}
