<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-700">Support officer profile</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $officer->name }}</h2>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.support-officers.edit', $officer) }}" class="inline-flex w-fit justify-center rounded-md bg-rose-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-800">Edit Officer</a>
                <a href="{{ route('admin.support-officers.index') }}" class="inline-flex w-fit justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">All Officers</a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="space-y-6 lg:col-span-2">
                @include('admin.partials.feedback')

                <article class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-status-badge :status="$officer->account_status" />
                        <span class="text-sm text-gray-600">Support officer</span>
                    </div>

                    <dl class="mt-6 grid gap-5 border-y border-gray-200 py-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Full Name</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $officer->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Email</dt>
                            <dd class="mt-1 break-all text-sm text-gray-600">{{ $officer->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $officer->phone ?: 'Not recorded' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Gender</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $officer->gender ? str($officer->gender)->headline() : 'Not recorded' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Staff ID</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $officer->staff_id ?: 'Not set' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Role</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ str($officer->role)->headline() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Specialization</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $officer->specialization ?: 'Not set' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Date Joined</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $officer->date_joined?->format('M j, Y') ?? 'Not set' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Account Created</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $officer->created_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Assigned Cases</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $officer->assigned_support_cases_count }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">
                        Passwords are hashed by Laravel and are not displayed from this screen. Deactivation preserves historical case assignments.
                    </div>
                </article>

                <section class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Recently Assigned Cases</h3>
                            <p class="mt-1 text-sm text-gray-600">References and workflow metadata only.</p>
                        </div>
                        <a href="{{ route('admin.cases.index', ['assigned_officer' => $officer->id]) }}" class="text-sm font-semibold text-rose-700 hover:text-rose-800">Case queue</a>
                    </div>

                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-normal text-gray-500">
                                    <th scope="col" class="py-3 pr-4">Reference</th>
                                    <th scope="col" class="px-4 py-3">Type</th>
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
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ str($case->urgency)->headline() }}</td>
                                        <td class="whitespace-nowrap px-4 py-3"><x-status-badge :status="$case->status" /></td>
                                        <td class="whitespace-nowrap py-3 pl-4 text-gray-600">{{ $case->updated_at->format('M j, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-sm text-gray-500">This officer has no assigned support cases.</td>
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
                    <p class="mt-2 text-sm leading-6 text-gray-600">Deactivate officers when they should no longer access staff areas.</p>

                    <div class="mt-5">
                        @if ($officer->account_status === \App\Models\User::STATUS_ACTIVE)
                            <form method="POST" action="{{ route('admin.support-officers.deactivate', $officer) }}">
                                @csrf
                                @method('PATCH')
                                <x-primary-button>Deactivate Officer</x-primary-button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.support-officers.activate', $officer) }}">
                                @csrf
                                @method('PATCH')
                                <x-primary-button>Activate Officer</x-primary-button>
                            </form>
                        @endif
                    </div>
                </section>

                <section class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Assigned Case Summary</h3>
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
