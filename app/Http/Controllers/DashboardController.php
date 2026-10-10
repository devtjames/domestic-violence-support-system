<?php

namespace App\Http\Controllers;

use App\Models\AbuseType;
use App\Models\AwarenessArticle;
use App\Models\CaseActivity;
use App\Models\CaseMessage;
use App\Models\SupportCase;
use App\Models\SupportResource;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function user(): View
    {
        /** @var User $user */
        $user = Auth::user();

        return view('dashboard', [
            'summaryCards' => $this->userSummaryCards($user),
            'recentCases' => SupportCase::query()
                ->ownedBy($user)
                ->with(['abuseType:id,name'])
                ->withCount(['messages as unread_messages_count' => fn ($query) => $query->unreadFor($user)])
                ->latest('updated_at')
                ->take(5)
                ->get(),
            'recentMessages' => CaseMessage::query()
                ->whereHas('supportCase', fn ($query) => $query->ownedBy($user))
                ->with(['supportCase:id,case_reference,user_id,status', 'sender:id,name,role'])
                ->latest()
                ->take(5)
                ->get(),
            'recentCaseUpdates' => CaseActivity::query()
                ->whereIn('action_type', $this->userVisibleCaseActivityTypes())
                ->whereHas('supportCase', fn ($query) => $query->ownedBy($user))
                ->with(['supportCase:id,case_reference,user_id,status'])
                ->latest()
                ->take(5)
                ->get(),
            'awarenessArticles' => AwarenessArticle::query()
                ->published()
                ->with('category:id,name')
                ->latest('published_at')
                ->take(3)
                ->get(),
            'supportResources' => SupportResource::query()
                ->active()
                ->orderBy('organization_name')
                ->take(3)
                ->get(),
        ]);
    }

    public function officer(): View
    {
        /** @var User $officer */
        $officer = Auth::user();

        return view('officer.dashboard', [
            'summaryCards' => $this->officerSummaryCards($officer),
            'casesAwaitingResponse' => SupportCase::query()
                ->where('assigned_officer_id', $officer->id)
                ->whereIn('status', SupportCase::openStatuses())
                ->whereHas('messages', fn ($query) => $query->unreadFor($officer))
                ->with(['abuseType:id,name'])
                ->withCount(['messages as unread_messages_count' => fn ($query) => $query->unreadFor($officer)])
                ->latest('updated_at')
                ->take(5)
                ->get(),
            'recentAssignments' => SupportCase::query()
                ->where('assigned_officer_id', $officer->id)
                ->with(['abuseType:id,name'])
                ->latest('updated_at')
                ->take(5)
                ->get(),
            'recentlyUpdatedCases' => SupportCase::query()
                ->visibleToOfficer($officer)
                ->with(['abuseType:id,name', 'assignedOfficer:id,name'])
                ->withCount(['messages as unread_messages_count' => fn ($query) => $query->unreadFor($officer)])
                ->latest('updated_at')
                ->take(6)
                ->get(),
        ]);
    }

    public function admin(): View
    {
        return view('admin.dashboard', [
            'summaryCards' => $this->adminSummaryCards(),
            'caseStatusCounts' => $this->caseStatusCounts(),
            'caseUrgencyCounts' => $this->caseUrgencyCounts(),
            'caseSourceCounts' => $this->caseSourceCounts(),
            'caseTypeCounts' => $this->caseTypeCounts(),
            'casesByMonth' => $this->casesByMonth(),
            'recentCases' => SupportCase::query()
                ->with(['abuseType:id,name', 'assignedOfficer:id,name'])
                ->latest('updated_at')
                ->take(6)
                ->get(),
            'recentActivities' => CaseActivity::query()
                ->with(['supportCase:id,case_reference', 'actor:id,name,role'])
                ->latest()
                ->take(8)
                ->get(),
        ]);
    }

    /**
     * @return array<int, array{label: string, value: int, tone: string}>
     */
    private function adminSummaryCards(): array
    {
        return [
            [
                'label' => 'Total Users',
                'value' => User::query()->where('role', User::ROLE_USER)->count(),
                'tone' => 'rose',
            ],
            [
                'label' => 'Support Officers',
                'value' => User::query()->where('role', User::ROLE_SUPPORT_OFFICER)->count(),
                'tone' => 'indigo',
            ],
            [
                'label' => 'Total Cases',
                'value' => SupportCase::query()->count(),
                'tone' => 'slate',
            ],
            [
                'label' => 'Open Cases',
                'value' => SupportCase::query()->whereIn('status', SupportCase::openStatuses())->count(),
                'tone' => 'amber',
            ],
            [
                'label' => 'Under Review',
                'value' => SupportCase::query()->where('status', SupportCase::STATUS_UNDER_REVIEW)->count(),
                'tone' => 'sky',
            ],
            [
                'label' => 'In Progress',
                'value' => SupportCase::query()->where('status', SupportCase::STATUS_IN_PROGRESS)->count(),
                'tone' => 'cyan',
            ],
            [
                'label' => 'Resolved Cases',
                'value' => SupportCase::query()->where('status', SupportCase::STATUS_RESOLVED)->count(),
                'tone' => 'emerald',
            ],
            [
                'label' => 'Anonymous Cases',
                'value' => SupportCase::query()->where('is_anonymous', true)->count(),
                'tone' => 'violet',
            ],
            [
                'label' => 'Registered Cases',
                'value' => SupportCase::query()->where('is_anonymous', false)->count(),
                'tone' => 'teal',
            ],
            [
                'label' => 'High-Priority Cases',
                'value' => SupportCase::query()
                    ->whereIn('urgency', [SupportCase::URGENCY_HIGH, SupportCase::URGENCY_CRITICAL])
                    ->count(),
                'tone' => 'red',
            ],
            [
                'label' => 'Published Articles',
                'value' => AwarenessArticle::query()->published()->count(),
                'tone' => 'fuchsia',
            ],
            [
                'label' => 'Support Resources',
                'value' => SupportResource::query()->active()->count(),
                'tone' => 'lime',
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, value: int, helper: string, tone: string}>
     */
    private function officerSummaryCards(User $officer): array
    {
        return [
            [
                'label' => 'Assigned Cases',
                'value' => SupportCase::query()
                    ->where('assigned_officer_id', $officer->id)
                    ->count(),
                'helper' => 'Cases currently assigned to you.',
                'tone' => 'indigo',
            ],
            [
                'label' => 'Open Assigned Cases',
                'value' => SupportCase::query()
                    ->where('assigned_officer_id', $officer->id)
                    ->whereIn('status', SupportCase::openStatuses())
                    ->count(),
                'helper' => 'Assigned cases still in active workflow.',
                'tone' => 'sky',
            ],
            [
                'label' => 'Open Case Queue',
                'value' => SupportCase::query()
                    ->whereNull('assigned_officer_id')
                    ->whereIn('status', SupportCase::openStatuses())
                    ->count(),
                'helper' => 'Unassigned open cases available for review.',
                'tone' => 'teal',
            ],
            [
                'label' => 'High-Priority Cases',
                'value' => SupportCase::query()
                    ->visibleToOfficer($officer)
                    ->whereIn('urgency', [SupportCase::URGENCY_HIGH, SupportCase::URGENCY_CRITICAL])
                    ->count(),
                'helper' => 'High and critical cases visible to you.',
                'tone' => 'red',
            ],
            [
                'label' => 'Awaiting Response',
                'value' => SupportCase::query()
                    ->where('assigned_officer_id', $officer->id)
                    ->whereIn('status', SupportCase::openStatuses())
                    ->whereHas('messages', fn ($query) => $query->unreadFor($officer))
                    ->count(),
                'helper' => 'Assigned cases with unread user messages.',
                'tone' => 'amber',
            ],
            [
                'label' => 'Unread Messages',
                'value' => CaseMessage::query()
                    ->unreadFor($officer)
                    ->whereHas('supportCase', fn ($query) => $query->visibleToOfficer($officer))
                    ->count(),
                'helper' => 'Unread messages in authorized cases.',
                'tone' => 'violet',
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, value: int, helper: string, tone: string}>
     */
    private function userSummaryCards(User $user): array
    {
        return [
            [
                'label' => 'Active Cases',
                'value' => SupportCase::query()
                    ->ownedBy($user)
                    ->whereIn('status', SupportCase::openStatuses())
                    ->count(),
                'helper' => 'Support requests still being handled.',
                'tone' => 'teal',
            ],
            [
                'label' => 'Resolved Cases',
                'value' => SupportCase::query()
                    ->ownedBy($user)
                    ->whereIn('status', SupportCase::terminalStatuses())
                    ->count(),
                'helper' => 'Requests marked resolved or closed.',
                'tone' => 'emerald',
            ],
            [
                'label' => 'Unread Messages',
                'value' => CaseMessage::query()
                    ->unreadFor($user)
                    ->whereHas('supportCase', fn ($query) => $query->ownedBy($user))
                    ->count(),
                'helper' => 'New private replies in your cases.',
                'tone' => 'indigo',
            ],
            [
                'label' => 'Total Cases',
                'value' => SupportCase::query()
                    ->ownedBy($user)
                    ->count(),
                'helper' => 'All support requests on your account.',
                'tone' => 'slate',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function userVisibleCaseActivityTypes(): array
    {
        return [
            CaseActivity::ACTION_CASE_SUBMITTED,
            CaseActivity::ACTION_CASE_ASSIGNED,
            CaseActivity::ACTION_CASE_REASSIGNED,
            CaseActivity::ACTION_STATUS_CHANGED,
            CaseActivity::ACTION_URGENCY_CHANGED,
            CaseActivity::ACTION_CASE_REFERRED,
            CaseActivity::ACTION_CASE_RESOLVED,
            CaseActivity::ACTION_CASE_CLOSED,
            CaseActivity::ACTION_CASE_REOPENED,
            CaseActivity::ACTION_USER_RESPONDED,
            CaseActivity::ACTION_OFFICER_RESPONDED,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function caseStatusCounts(): array
    {
        $counts = SupportCase::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return collect(SupportCase::statuses())
            ->mapWithKeys(fn (string $status): array => [str($status)->headline()->toString() => (int) ($counts[$status] ?? 0)])
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function caseUrgencyCounts(): array
    {
        $counts = SupportCase::query()
            ->selectRaw('urgency, count(*) as aggregate')
            ->groupBy('urgency')
            ->pluck('aggregate', 'urgency');

        return collect(SupportCase::urgencies())
            ->mapWithKeys(fn (string $urgency): array => [str($urgency)->headline()->toString() => (int) ($counts[$urgency] ?? 0)])
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function caseSourceCounts(): array
    {
        return [
            'Registered' => SupportCase::query()->where('is_anonymous', false)->count(),
            'Anonymous' => SupportCase::query()->where('is_anonymous', true)->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function caseTypeCounts(): array
    {
        return AbuseType::query()
            ->withCount('supportCases')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (AbuseType $abuseType): array => [$abuseType->name => $abuseType->support_cases_count])
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function casesByMonth(): array
    {
        $start = CarbonImmutable::now()->startOfMonth()->subMonths(5);
        $counts = SupportCase::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->groupBy(fn (SupportCase $supportCase): string => $supportCase->created_at->format('Y-m'))
            ->map->count();

        return collect(range(0, 5))
            ->mapWithKeys(function (int $offset) use ($start, $counts): array {
                $month = $start->addMonths($offset);

                return [$month->format('M Y') => (int) ($counts[$month->format('Y-m')] ?? 0)];
            })
            ->all();
    }
}
