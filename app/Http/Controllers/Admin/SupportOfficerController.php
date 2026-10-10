<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSupportOfficerRequest;
use App\Http\Requests\Admin\UpdateSupportOfficerRequest;
use App\Models\AuditLog;
use App\Models\SupportCase;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportOfficerController extends Controller
{
    public function index(Request $request): View
    {
        $sort = $this->normalizedSort($request->string('sort')->toString(), $this->officerSortOptions());

        $query = User::query()
            ->where('role', User::ROLE_SUPPORT_OFFICER)
            ->withCount('assignedSupportCases')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('staff_id', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('account_status'), fn ($query) => $query->where('account_status', $request->string('account_status')->toString()))
            ->when($request->filled('specialization'), fn ($query) => $query->where('specialization', $request->string('specialization')->toString()));

        $this->applyOfficerSort($query, $sort);

        $officers = $query
            ->paginate(12)
            ->withQueryString();

        return view('admin.support-officers.index', [
            'officers' => $officers,
            'accountStatuses' => User::accountStatuses(),
            'specializations' => User::supportOfficerSpecializations(),
            'sortOptions' => $this->officerSortOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.support-officers.create', [
            'officer' => new User([
                'account_status' => User::STATUS_ACTIVE,
                'date_joined' => now()->toDateString(),
            ]),
            'accountStatuses' => User::accountStatuses(),
            'specializations' => User::supportOfficerSpecializations(),
            'genders' => User::genders(),
        ]);
    }

    public function store(StoreSupportOfficerRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['account_status', 'password', 'password_confirmation']);
        $officer = new User($data);

        $officer->forceFill([
            'role' => User::ROLE_SUPPORT_OFFICER,
            'account_status' => $request->validated('account_status'),
            'password' => $request->validated('password'),
            'email_verified_at' => now(),
        ])->save();

        app(AuditLogger::class)->record(
            $request,
            AuditLog::MODULE_SUPPORT_OFFICERS,
            'officer_created',
            'Support officer account created.',
            $officer,
            ['staff_id' => $officer->staff_id, 'specialization' => $officer->specialization]
        );

        return redirect()
            ->route('admin.support-officers.show', $officer)
            ->with('status', 'Support officer created.');
    }

    public function show(User $supportOfficer): View
    {
        $this->ensureSupportOfficer($supportOfficer);

        return view('admin.support-officers.show', [
            'officer' => $supportOfficer->loadCount('assignedSupportCases'),
            'caseStatusCounts' => $this->assignedCaseStatusCounts($supportOfficer),
            'recentCases' => SupportCase::query()
                ->where('assigned_officer_id', $supportOfficer->id)
                ->with(['abuseType:id,name'])
                ->latest('updated_at')
                ->take(6)
                ->get(),
        ]);
    }

    public function edit(User $supportOfficer): View
    {
        $this->ensureSupportOfficer($supportOfficer);

        return view('admin.support-officers.edit', [
            'officer' => $supportOfficer,
            'accountStatuses' => User::accountStatuses(),
            'specializations' => User::supportOfficerSpecializations(),
            'genders' => User::genders(),
        ]);
    }

    public function update(UpdateSupportOfficerRequest $request, User $supportOfficer): RedirectResponse
    {
        $this->ensureSupportOfficer($supportOfficer);

        $previousStatus = $supportOfficer->account_status;
        $previousSpecialization = $supportOfficer->specialization;

        $supportOfficer->forceFill($request->validated())->save();

        app(AuditLogger::class)->record(
            $request,
            AuditLog::MODULE_SUPPORT_OFFICERS,
            'officer_updated',
            'Support officer account updated.',
            $supportOfficer,
            [
                'previous_status' => $previousStatus,
                'new_status' => $supportOfficer->account_status,
                'previous_specialization' => $previousSpecialization,
                'new_specialization' => $supportOfficer->specialization,
            ]
        );

        return redirect()
            ->route('admin.support-officers.show', $supportOfficer)
            ->with('status', 'Support officer updated.');
    }

    public function activate(User $supportOfficer): RedirectResponse
    {
        $this->ensureSupportOfficer($supportOfficer);

        $previousStatus = $supportOfficer->account_status;

        $supportOfficer->forceFill(['account_status' => User::STATUS_ACTIVE])->save();

        app(AuditLogger::class)->record(
            request(),
            AuditLog::MODULE_SUPPORT_OFFICERS,
            'officer_activated',
            'Support officer account activated.',
            $supportOfficer,
            ['previous_status' => $previousStatus, 'new_status' => User::STATUS_ACTIVE]
        );

        return back()->with('status', 'Support officer activated.');
    }

    public function deactivate(User $supportOfficer): RedirectResponse
    {
        $this->ensureSupportOfficer($supportOfficer);

        $previousStatus = $supportOfficer->account_status;

        $supportOfficer->forceFill(['account_status' => User::STATUS_DISABLED])->save();

        app(AuditLogger::class)->record(
            request(),
            AuditLog::MODULE_SUPPORT_OFFICERS,
            'officer_deactivated',
            'Support officer account deactivated.',
            $supportOfficer,
            ['previous_status' => $previousStatus, 'new_status' => User::STATUS_DISABLED]
        );

        return back()->with('status', 'Support officer deactivated. Historical case assignments are preserved.');
    }

    private function ensureSupportOfficer(User $supportOfficer): void
    {
        abort_unless($supportOfficer->isSupportOfficer(), 404);
    }

    /**
     * @return array<string, int>
     */
    private function assignedCaseStatusCounts(User $supportOfficer): array
    {
        $counts = SupportCase::query()
            ->where('assigned_officer_id', $supportOfficer->id)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return collect(SupportCase::statuses())
            ->mapWithKeys(fn (string $status): array => [str($status)->headline()->toString() => (int) ($counts[$status] ?? 0)])
            ->all();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<User>  $query
     */
    private function applyOfficerSort($query, string $sort): void
    {
        match ($sort) {
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'name' => $query->orderBy('name')->orderBy('email'),
            'staff_id' => $query->orderBy('staff_id')->orderBy('name'),
            'specialization' => $query->orderBy('specialization')->orderBy('name'),
            'cases_desc' => $query->orderByDesc('assigned_support_cases_count')->orderBy('name'),
            'cases_asc' => $query->orderBy('assigned_support_cases_count')->orderBy('name'),
            'date_joined' => $query->orderByDesc('date_joined')->orderBy('name'),
            'status' => $query->orderBy('account_status')->orderBy('name'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };
    }

    /**
     * @return array<string, string>
     */
    private function officerSortOptions(): array
    {
        return [
            'newest' => 'Newest created',
            'oldest' => 'Oldest created',
            'name' => 'Name',
            'staff_id' => 'Staff ID',
            'specialization' => 'Specialization',
            'cases_desc' => 'Most assigned cases',
            'cases_asc' => 'Fewest assigned cases',
            'date_joined' => 'Date joined',
            'status' => 'Status',
        ];
    }

    /**
     * @param  array<string, string>  $options
     */
    private function normalizedSort(string $sort, array $options): string
    {
        return array_key_exists($sort, $options) ? $sort : 'newest';
    }
}
