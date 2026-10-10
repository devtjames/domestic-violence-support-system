<?php

namespace App\Http\Controllers;

use App\Http\Requests\CaseIndexFilterRequest;
use App\Http\Requests\StoreSupportCaseRequest;
use App\Models\AbuseType;
use App\Models\CaseActivity;
use App\Models\SupportCase;
use App\Models\SystemSetting;
use App\Services\CaseAttachmentService;
use App\Services\CaseNotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class SupportCaseController extends Controller
{
    public function index(CaseIndexFilterRequest $request): View
    {
        $filters = $request->validated();

        $query = SupportCase::query()
            ->ownedBy($request->user())
            ->with('abuseType')
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
            ->paginate(10)
            ->withQueryString();

        return view('cases.index', [
            'cases' => $cases,
            'abuseTypes' => AbuseType::query()->orderBy('name')->get(),
            'statuses' => SupportCase::statuses(),
            'urgencies' => SupportCase::urgencies(),
            'sortOptions' => $this->caseSortOptions(),
        ]);
    }

    public function create(): View
    {
        return view('cases.create', [
            'case' => new SupportCase([
                'urgency' => SupportCase::URGENCY_LOW,
                'preferred_contact_method' => SupportCase::CONTACT_PLATFORM_MESSAGE,
            ]),
            'abuseTypes' => AbuseType::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'urgencies' => SupportCase::urgencies(),
            'contactMethods' => SupportCase::contactMethods(),
            'criticalCaseGuidance' => SystemSetting::getValue(SystemSetting::KEY_CRITICAL_CASE_GUIDANCE),
        ]);
    }

    public function store(StoreSupportCaseRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('attachments');
        $data['is_anonymous'] = false;
        $data['status'] = SupportCase::STATUS_SUBMITTED;
        $data['assigned_officer_id'] = null;

        try {
            $supportCase = DB::transaction(function () use ($request, $data) {
                $supportCase = $request->user()->supportCases()->create($data);
                $supportCase->recordActivity(
                    CaseActivity::ACTION_CASE_SUBMITTED,
                    'Registered user submitted a support request.',
                    $request->user(),
                    null,
                    SupportCase::STATUS_SUBMITTED,
                    $request
                );

                $attachments = app(CaseAttachmentService::class)->storeMany(
                    $supportCase,
                    $request->file('attachments', []),
                    $request->user()
                );

                if ($attachments !== []) {
                    $supportCase->recordActivity(
                        CaseActivity::ACTION_ATTACHMENT_ADDED,
                        count($attachments) === 1 ? 'Case attachment uploaded at submission.' : count($attachments).' case attachments uploaded at submission.',
                        $request->user(),
                        null,
                        (string) count($attachments),
                        $request
                    );
                }

                app(CaseNotificationService::class)->caseSubmitted($supportCase);

                return $supportCase;
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(['support_case' => 'We could not submit the support request. Please try again.']);
        }

        return redirect()
            ->route('cases.show', $supportCase)
            ->with('status', 'Support request submitted. Your case reference is '.$supportCase->case_reference.'.');
    }

    public function show(Request $request, SupportCase $supportCase): View
    {
        abort_unless($supportCase->user_id === $request->user()->id, 403);

        $supportCase->markMessagesReadFor($request->user());

        return view('cases.show', [
            'case' => $supportCase->load(['abuseType', 'assignedOfficer', 'messages.sender', 'messages.receiver', 'attachments.uploader']),
            'contactMethods' => SupportCase::contactMethods(),
        ]);
    }

    public function edit(SupportCase $supportCase): RedirectResponse
    {
        return redirect()->route('cases.show', $supportCase);
    }

    public function update(Request $request, SupportCase $supportCase): RedirectResponse
    {
        return redirect()->route('cases.show', $supportCase);
    }

    public function destroy(SupportCase $supportCase): RedirectResponse
    {
        return redirect()->route('cases.show', $supportCase);
    }

    /**
     * @param  Builder<SupportCase>  $query
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
