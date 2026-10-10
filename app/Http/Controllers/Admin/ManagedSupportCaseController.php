<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CaseQueueFilterRequest;
use App\Http\Requests\Admin\UpdateManagedSupportCaseRequest;
use App\Models\AbuseType;
use App\Models\AuditLog;
use App\Models\CaseActivity;
use App\Models\SupportCase;
use App\Models\User;
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
            ->with(['abuseType', 'user', 'assignedOfficer'])
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters) {
                $search = $filters['search'];

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('case_reference', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('anonymous_nickname', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->when(filled($filters['urgency'] ?? null), fn ($query) => $query->where('urgency', $filters['urgency']))
            ->when(filled($filters['abuse_type'] ?? null), fn ($query) => $query->where('abuse_type_id', $filters['abuse_type']))
            ->when(filled($filters['assigned_officer'] ?? null), function ($query) use ($filters) {
                if ($filters['assigned_officer'] === 'unassigned') {
                    $query->whereNull('assigned_officer_id');

                    return;
                }

                $query->where('assigned_officer_id', (int) $filters['assigned_officer']);
            })
            ->when(filled($filters['source'] ?? null), fn ($query) => $query->where('is_anonymous', $filters['source'] === 'anonymous'))
            ->when(filled($filters['date_from'] ?? null), fn ($query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn ($query) => $query->whereDate('created_at', '<=', $filters['date_to']));

        $this->applyCaseSort($query, $filters['sort'] ?? 'newest');

        $cases = $query
            ->paginate(12)
            ->withQueryString();

        return view('admin.cases.index', [
            'cases' => $cases,
            'abuseTypes' => AbuseType::query()->orderBy('name')->get(),
            'officers' => $this->supportOfficers(),
            'statuses' => SupportCase::statuses(),
            'urgencies' => SupportCase::urgencies(),
            'sortOptions' => $this->caseSortOptions(),
        ]);
    }

    public function show(SupportCase $supportCase): View
    {
        return view('admin.cases.show', [
            'case' => $supportCase->load(['abuseType', 'user', 'assignedOfficer', 'activities.actor', 'messages.sender', 'messages.receiver', 'notes.author', 'attachments.uploader']),
            'officers' => $this->supportOfficers(),
            'statuses' => SupportCase::statuses(),
            'urgencies' => SupportCase::urgencies(),
            'contactMethods' => SupportCase::contactMethods(),
        ]);
    }

    public function update(UpdateManagedSupportCaseRequest $request, SupportCase $supportCase): RedirectResponse
    {
        DB::transaction(function () use ($request, $supportCase) {
            $data = $request->safe()->except('action_note');
            $original = $supportCase->only(['assigned_officer_id', 'urgency', 'status']);

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
            ->route('admin.cases.show', $supportCase)
            ->with('status', 'Support case updated.');
    }

    /**
     * @param  array<string, mixed>  $original
     */
    private function recordChanges(SupportCase $supportCase, array $original, UpdateManagedSupportCaseRequest $request): void
    {
        $note = $request->string('action_note')->toString();

        if ((int) ($original['assigned_officer_id'] ?? 0) !== (int) ($supportCase->assigned_officer_id ?? 0)) {
            $action = $original['assigned_officer_id'] ? CaseActivity::ACTION_CASE_REASSIGNED : CaseActivity::ACTION_CASE_ASSIGNED;
            $supportCase->recordActivity(
                $action,
                $note ?: ($action === CaseActivity::ACTION_CASE_REASSIGNED ? 'Case reassigned to another support officer.' : 'Case assigned to a support officer.'),
                $request->user(),
                $this->officerLabel($original['assigned_officer_id']),
                $this->officerLabel($supportCase->assigned_officer_id),
                $request
            );

            app(CaseNotificationService::class)->assignmentChanged($supportCase, $original['assigned_officer_id']);

            app(AuditLogger::class)->record(
                $request,
                AuditLog::MODULE_CASES,
                $action === CaseActivity::ACTION_CASE_REASSIGNED ? 'case_reassigned' : 'case_assigned',
                'Support case assignment changed for '.$supportCase->case_reference.'.',
                $supportCase,
                [
                    'previous_officer' => $this->officerLabel($original['assigned_officer_id']),
                    'new_officer' => $this->officerLabel($supportCase->assigned_officer_id),
                ]
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

    private function officerLabel(mixed $officerId): string
    {
        if (! $officerId) {
            return 'Unassigned';
        }

        return User::query()->find($officerId)?->name ?? 'Unknown officer';
    }

    private function supportOfficers()
    {
        return User::query()
            ->where('role', User::ROLE_SUPPORT_OFFICER)
            ->where('account_status', User::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
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
