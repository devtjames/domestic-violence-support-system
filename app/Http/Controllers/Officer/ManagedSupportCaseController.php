<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Officer\CaseQueueFilterRequest;
use App\Http\Requests\Officer\UpdateManagedSupportCaseRequest;
use App\Models\AbuseType;
use App\Models\AuditLog;
use App\Models\CaseActivity;
use App\Models\SupportCase;
use App\Services\AuditLogger;
use App\Services\CaseNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ManagedSupportCaseController extends Controller
{
    public function index(CaseQueueFilterRequest $request): View
    {
        $filters = $request->validated();

        $query = SupportCase::query()
            ->visibleToOfficer($request->user())
            ->with(['abuseType', 'user', 'assignedOfficer'])
            ->withCount([
                'messages as unread_messages_count' => fn ($query) => $query->unreadFor($request->user()),
            ])
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters) {
                $search = $filters['search'];

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('case_reference', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->when(filled($filters['urgency'] ?? null), fn ($query) => $query->where('urgency', $filters['urgency']))
            ->when(filled($filters['abuse_type'] ?? null), fn ($query) => $query->where('abuse_type_id', $filters['abuse_type']))
            ->when(filled($filters['date_from'] ?? null), fn ($query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn ($query) => $query->whereDate('created_at', '<=', $filters['date_to']));

        $this->applyCaseSort($query, $filters['sort'] ?? 'newest');

        $cases = $query
            ->paginate(12)
            ->withQueryString();

        return view('officer.cases.index', [
            'cases' => $cases,
            'abuseTypes' => AbuseType::query()->orderBy('name')->get(),
            'statuses' => SupportCase::statuses(),
            'urgencies' => SupportCase::urgencies(),
            'sortOptions' => $this->caseSortOptions(),
        ]);
    }

    public function show(Request $request, SupportCase $supportCase): View
    {
        abort_unless($supportCase->canBeManagedByOfficer($request->user()), 403);

        $supportCase->markMessagesReadFor($request->user());

        return view('officer.cases.show', [
            'case' => $supportCase->load(['abuseType', 'user', 'assignedOfficer', 'activities.actor', 'messages.sender', 'messages.receiver', 'notes.author', 'attachments.uploader']),
            'statuses' => [
                SupportCase::STATUS_UNDER_REVIEW,
                SupportCase::STATUS_IN_PROGRESS,
                SupportCase::STATUS_AWAITING_USER,
                SupportCase::STATUS_REFERRED,
                SupportCase::STATUS_RESOLVED,
                SupportCase::STATUS_CLOSED,
            ],
            'urgencies' => SupportCase::urgencies(),
            'contactMethods' => SupportCase::contactMethods(),
        ]);
    }

    public function update(UpdateManagedSupportCaseRequest $request, SupportCase $supportCase): RedirectResponse
    {
        DB::transaction(function () use ($request, $supportCase) {
            $original = $supportCase->only(['assigned_officer_id', 'urgency', 'status']);
            $data = $request->safe()->except('action_note');

            if ($supportCase->assigned_officer_id === null) {
                $data['assigned_officer_id'] = $request->user()->id;
            }

            if (in_array($data['status'], [SupportCase::STATUS_RESOLVED, SupportCase::STATUS_CLOSED], true)) {
                $data['resolved_at'] = $supportCase->resolved_at ?? now();
            }

            if ($data['status'] === SupportCase::STATUS_CLOSED) {
                $data['closed_at'] = $supportCase->closed_at ?? now();
            }

            if (
                SupportCase::isTerminalStatus((string) $original['status'])
                && SupportCase::isOpenStatus((string) $data['status'])
            ) {
                $data['resolved_at'] = null;
                $data['closed_at'] = null;
            }

            $supportCase->update($data);

            $this->recordChanges($supportCase->refresh(), $original, $request);
        });

        return redirect()
            ->route('officer.cases.show', $supportCase)
            ->with('status', 'Support case updated.');
    }

    public function accept(Request $request, SupportCase $supportCase): RedirectResponse
    {
        abort_unless($supportCase->canBeManagedByOfficer($request->user()), 403);
        abort_unless($supportCase->assigned_officer_id === null, 403);

        $previousStatus = $supportCase->status;

        $supportCase->update([
            'assigned_officer_id' => $request->user()->id,
            'status' => SupportCase::STATUS_ASSIGNED,
        ]);

        $supportCase->recordActivity(
            CaseActivity::ACTION_CASE_ASSIGNED,
            'Support officer accepted the case.',
            $request->user(),
            'Unassigned',
            $request->user()->name,
            $request
        );

        $supportCase->recordActivity(
            CaseActivity::ACTION_STATUS_CHANGED,
            'Case status changed after officer acceptance.',
            $request->user(),
            $previousStatus,
            SupportCase::STATUS_ASSIGNED,
            $request
        );

        app(CaseNotificationService::class)->officerAcceptedCase($supportCase->refresh(), $request->user());
        app(CaseNotificationService::class)->statusChanged($supportCase, $previousStatus, $request->user());

        app(AuditLogger::class)->record(
            $request,
            AuditLog::MODULE_CASES,
            'case_accepted',
            'Support officer accepted case '.$supportCase->case_reference.'.',
            $supportCase,
            ['previous_status' => $previousStatus, 'new_status' => SupportCase::STATUS_ASSIGNED]
        );

        return redirect()
            ->route('officer.cases.show', $supportCase)
            ->with('status', 'Case accepted.');
    }

    /**
     * @param  array<string, mixed>  $original
     */
    private function recordChanges(SupportCase $supportCase, array $original, UpdateManagedSupportCaseRequest $request): void
    {
        $note = $request->string('action_note')->toString();

        if ((int) ($original['assigned_officer_id'] ?? 0) !== (int) ($supportCase->assigned_officer_id ?? 0)) {
            $supportCase->recordActivity(
                CaseActivity::ACTION_CASE_ASSIGNED,
                $note ?: 'Support officer accepted the case.',
                $request->user(),
                'Unassigned',
                $request->user()->name,
                $request
            );

            app(CaseNotificationService::class)->officerAcceptedCase($supportCase, $request->user());

            app(AuditLogger::class)->record(
                $request,
                AuditLog::MODULE_CASES,
                'case_accepted',
                'Support officer accepted case '.$supportCase->case_reference.'.',
                $supportCase,
                ['previous_assignment' => 'Unassigned', 'new_assignment' => $request->user()->name]
            );
        }

        if ($original['urgency'] !== $supportCase->urgency) {
            $supportCase->recordActivity(
                CaseActivity::ACTION_URGENCY_CHANGED,
                $note ?: 'Case urgency changed.',
                $request->user(),
                (string) $original['urgency'],
                $supportCase->urgency,
                $request
            );

            app(CaseNotificationService::class)->urgencyChanged($supportCase, (string) $original['urgency']);

            app(AuditLogger::class)->record(
                $request,
                AuditLog::MODULE_CASES,
                'case_urgency_changed',
                'Support case urgency changed for '.$supportCase->case_reference.'.',
                $supportCase,
                ['previous_urgency' => (string) $original['urgency'], 'new_urgency' => $supportCase->urgency]
            );
        }

        if ($original['status'] !== $supportCase->status) {
            $action = match ($supportCase->status) {
                SupportCase::STATUS_REFERRED => CaseActivity::ACTION_CASE_REFERRED,
                SupportCase::STATUS_RESOLVED => CaseActivity::ACTION_CASE_RESOLVED,
                SupportCase::STATUS_CLOSED => CaseActivity::ACTION_CASE_CLOSED,
                default => SupportCase::isTerminalStatus((string) $original['status']) && SupportCase::isOpenStatus($supportCase->status)
                    ? CaseActivity::ACTION_CASE_REOPENED
                    : CaseActivity::ACTION_STATUS_CHANGED,
            };

            $supportCase->recordActivity(
                $action,
                $note ?: ($action === CaseActivity::ACTION_CASE_REOPENED ? 'Case reopened for further support.' : 'Case status changed.'),
                $request->user(),
                (string) $original['status'],
                $supportCase->status,
                $request
            );

            app(CaseNotificationService::class)->statusChanged($supportCase, (string) $original['status'], $request->user());

            app(AuditLogger::class)->record(
                $request,
                AuditLog::MODULE_CASES,
                'case_status_changed',
                'Support case status changed for '.$supportCase->case_reference.'.',
                $supportCase,
                ['previous_status' => (string) $original['status'], 'new_status' => $supportCase->status]
            );
        }
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<SupportCase>  $query
     */
    private function applyCaseSort($query, string $sort): void
    {
        match ($sort) {
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'recently_updated' => $query->orderByDesc('updated_at')->orderByDesc('id'),
            'oldest_updated' => $query->orderBy('updated_at')->orderBy('id'),
            'urgency_high' => $query->orderByRaw("CASE urgency WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")->orderByDesc('created_at'),
            'urgency_low' => $query->orderByRaw("CASE urgency WHEN 'low' THEN 1 WHEN 'medium' THEN 2 WHEN 'high' THEN 3 ELSE 4 END")->orderByDesc('created_at'),
            'status' => $query->orderBy('status')->orderByDesc('updated_at'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };
    }

    /**
     * @return array<string, string>
     */
    private function caseSortOptions(): array
    {
        return [
            'newest' => 'Newest submitted',
            'oldest' => 'Oldest submitted',
            'recently_updated' => 'Recently updated',
            'oldest_updated' => 'Oldest updated',
            'urgency_high' => 'Highest urgency',
            'urgency_low' => 'Lowest urgency',
            'status' => 'Status',
        ];
    }
}
