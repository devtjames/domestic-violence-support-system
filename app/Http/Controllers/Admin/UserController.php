<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SupportCase;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $sort = $this->normalizedSort($request->string('sort')->toString(), $this->userSortOptions());

        $query = User::query()
            ->where('role', User::ROLE_USER)
            ->withCount('supportCases')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('account_status'), fn ($query) => $query->where('account_status', $request->string('account_status')->toString()));

        $this->applyUserSort($query, $sort);

        $users = $query
            ->paginate(12)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'accountStatuses' => User::accountStatuses(),
            'sortOptions' => $this->userSortOptions(),
        ]);
    }

    public function show(User $user): View
    {
        abort_unless($user->isRegisteredUser(), 404);

        return view('admin.users.show', [
            'managedUser' => $user->loadCount('supportCases'),
            'caseStatusCounts' => $this->caseStatusCounts($user),
            'recentCases' => SupportCase::query()
                ->ownedBy($user)
                ->with(['abuseType:id,name', 'assignedOfficer:id,name'])
                ->latest('updated_at')
                ->take(6)
                ->get(),
        ]);
    }

    public function activate(User $user): RedirectResponse
    {
        abort_unless($user->isRegisteredUser(), 404);

        $previousStatus = $user->account_status;

        $user->forceFill(['account_status' => User::STATUS_ACTIVE])->save();

        app(AuditLogger::class)->record(
            request(),
            AuditLog::MODULE_USERS,
            'user_activated',
            'Registered user account activated.',
            $user,
            ['previous_status' => $previousStatus, 'new_status' => User::STATUS_ACTIVE]
        );

        return back()->with('status', 'User account activated.');
    }

    public function deactivate(User $user): RedirectResponse
    {
        abort_unless($user->isRegisteredUser(), 404);

        $previousStatus = $user->account_status;

        $user->forceFill(['account_status' => User::STATUS_DISABLED])->save();

        app(AuditLogger::class)->record(
            request(),
            AuditLog::MODULE_USERS,
            'user_deactivated',
            'Registered user account deactivated.',
            $user,
            ['previous_status' => $previousStatus, 'new_status' => User::STATUS_DISABLED]
        );

        return back()->with('status', 'User account deactivated.');
    }

    /**
     * @return array<string, int>
     */
    private function caseStatusCounts(User $user): array
    {
        $counts = SupportCase::query()
            ->ownedBy($user)
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
    private function applyUserSort($query, string $sort): void
    {
        match ($sort) {
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'name' => $query->orderBy('name')->orderBy('email'),
            'email' => $query->orderBy('email'),
            'cases_desc' => $query->orderByDesc('support_cases_count')->orderBy('name'),
            'cases_asc' => $query->orderBy('support_cases_count')->orderBy('name'),
            'status' => $query->orderBy('account_status')->orderBy('name'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };
    }

    /**
     * @return array<string, string>
     */
    private function userSortOptions(): array
    {
        return [
            'newest' => 'Newest joined',
            'oldest' => 'Oldest joined',
            'name' => 'Name',
            'email' => 'Email',
            'cases_desc' => 'Most cases',
            'cases_asc' => 'Fewest cases',
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
