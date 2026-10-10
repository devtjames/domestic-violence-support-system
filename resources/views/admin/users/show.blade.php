<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-700">User profile</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $managedUser->name }}</h2>
            </div>
            <a href="{{ route('admin.users.index') }}" class="inline-flex w-fit justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">All Users</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="space-y-6 lg:col-span-2">
                @include('admin.partials.feedback')

                <article class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-status-badge :status="$managedUser->account_status" />
                        <span class="text-sm text-gray-600">Registered user</span>
                    </div>

                    <dl class="mt-6 grid gap-5 border-y border-gray-200 py-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Name</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $managedUser->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Email</dt>
                            <dd class="mt-1 break-all text-sm text-gray-600">{{ $managedUser->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Joined</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $managedUser->created_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Email Verified</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $managedUser->email_verified_at?->format('M j, Y g:i A') ?? 'Unverified' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $managedUser->updated_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Submitted Cases</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $managedUser->support_cases_count }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">
                        Passwords are hashed by Laravel and are not displayed or managed from this screen.
                    </div>
                </article>

                <section class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Recent Cases</h3>
                            <p class="mt-1 text-sm text-gray-600">References and workflow metadata only.</p>
                        </div>
                        <a href="{{ route('admin.cases.index', ['search' => $managedUser->email]) }}" class="text-sm font-semibold text-rose-700 hover:text-rose-800">Case queue</a>
                    </div>

                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-normal text-gray-500">
                                    <th scope="col" class="py-3 pr-4">Reference</th>
                                    <th scope="col" class="px-4 py-3">Type</th>
                                    <th scope="col" class="px-4 py-3">Officer</th>
                                    <th scope="col" class="px-4 py-3">Urgency</th>
                                    <th scope="col" class="px-4 py-3">Status</th>
                                    <th scope="col" class="py-3 pl-4">Updated</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($recentCases as $case)
                                    <tr>
                                        <td class="whitespace-nowrap py-3 pr-4">
                                            <a href="{{ route('admin.cases.show', $case) }}" class="font-semibold text-rose-700 hover:text-rose-800">{{ $case->case_reference }}</a>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $case->abuseType?->name ?? 'Unspecified' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $case->assignedOfficer?->name ?? 'Unassigned' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ str($case->urgency)->headline() }}</td>
                                        <td class="whitespace-nowrap px-4 py-3"><x-status-badge :status="$case->status" /></td>
                                        <td class="whitespace-nowrap py-3 pl-4 text-gray-600">{{ $case->updated_at->format('M j, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-sm text-gray-500">This user has not submitted any support cases.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Account Status</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600">Deactivate accounts instead of deleting user history.</p>

                    <div class="mt-5">
                        @if ($managedUser->account_status === \App\Models\User::STATUS_ACTIVE)
                            <form method="POST" action="{{ route('admin.users.deactivate', $managedUser) }}">
                                @csrf
                                @method('PATCH')
                                <x-primary-button>Deactivate Account</x-primary-button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.users.activate', $managedUser) }}">
                                @csrf
                                @method('PATCH')
                                <x-primary-button>Activate Account</x-primary-button>
                            </form>
                        @endif
                    </div>
                </section>

                <section class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Case Status Summary</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        @foreach ($caseStatusCounts as $status => $count)
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-gray-600">{{ $status }}</dt>
                                <dd class="font-semibold text-gray-900">{{ $count }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
