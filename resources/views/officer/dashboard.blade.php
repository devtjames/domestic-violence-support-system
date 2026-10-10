<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-indigo-700">Support officer area</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Officer Dashboard') }}
                </h2>
            </div>
            <span class="inline-flex w-fit rounded-md bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-700 ring-1 ring-emerald-200">
                {{ ucfirst(Auth::user()->account_status) }}
            </span>
        </div>
    </x-slot>

    @php
        $cardClasses = [
            'indigo' => 'bg-indigo-50 text-indigo-800 ring-indigo-100',
            'sky' => 'bg-sky-50 text-sky-800 ring-sky-100',
            'teal' => 'bg-teal-50 text-teal-800 ring-teal-100',
            'red' => 'bg-red-50 text-red-800 ring-red-100',
            'amber' => 'bg-amber-50 text-amber-800 ring-amber-100',
            'violet' => 'bg-violet-50 text-violet-800 ring-violet-100',
        ];
    @endphp

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg lg:col-span-2">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase text-indigo-700">Assigned access</p>
                            <h3 class="mt-2 text-2xl font-semibold text-gray-900">{{ Auth::user()->name }}</h3>
                            <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600">
                                Review assigned cases and the open queue according to backend access rules. Dashboard lists use references, workflow labels, and counts instead of private case narratives.
                            </p>
                        </div>
                        <a href="{{ route('officer.cases.index') }}" class="inline-flex w-fit justify-center rounded-md bg-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2">
                            Open Case Queue
                        </a>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-base font-semibold text-gray-900">Quick Links</h3>
                    <div class="mt-4 grid gap-3 text-sm">
                        <a href="{{ route('officer.cases.index') }}" class="font-medium text-indigo-700 hover:text-indigo-800">Case queue</a>
                        <a href="{{ route('public.resources') }}" class="font-medium text-indigo-700 hover:text-indigo-800">Support resources</a>
                        <a href="{{ route('profile.edit') }}" class="font-medium text-indigo-700 hover:text-indigo-800">Profile settings</a>
                    </div>
                </div>
            </section>

            <section class="metric-grid grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($summaryCards as $card)
                    <article class="metric-card rounded-lg p-5 ring-1 {{ $cardClasses[$card['tone']] ?? $cardClasses['indigo'] }}">
                        <p class="text-sm font-medium">{{ $card['label'] }}</p>
                        <p class="mt-3 text-3xl font-semibold tracking-normal">{{ number_format($card['value']) }}</p>
                        <p class="mt-2 text-sm leading-6">{{ $card['helper'] }}</p>
                    </article>
                @endforeach
            </section>

            <section class="grid gap-6 xl:grid-cols-5">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg xl:col-span-2">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Cases Awaiting Response</h3>
                            <p class="mt-1 text-sm text-gray-600">Assigned cases with unread messages for you.</p>
                        </div>
                        <a href="{{ route('officer.cases.index') }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-800">View queue</a>
                    </div>

                    <div class="mt-5 space-y-4">
                        @forelse ($casesAwaitingResponse as $case)
                            <article class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <a href="{{ route('officer.cases.show', $case) }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-800">{{ $case->case_reference }}</a>
                                        <p class="mt-1 text-sm text-gray-600">{{ $case->abuseType?->name ?? 'Unspecified' }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-md bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200">
                                        {{ $case->unread_messages_count }} unread
                                    </span>
                                </div>
                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <x-status-badge :status="$case->status" />
                                    <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold capitalize ring-1 {{ $case->urgency === 'critical' ? 'bg-rose-50 text-rose-700 ring-rose-200' : ($case->urgency === 'high' ? 'bg-amber-50 text-amber-700 ring-amber-200' : 'bg-gray-100 text-gray-700 ring-gray-200') }}">
                                        {{ str($case->urgency)->headline() }}
                                    </span>
                                </div>
                            </article>
                        @empty
                            <p class="rounded-md bg-gray-50 p-4 text-sm text-gray-600">No assigned cases currently have unread messages.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg xl:col-span-3">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Recently Updated Cases</h3>
                            <p class="mt-1 text-sm text-gray-600">Visible assigned cases and open queue items only.</p>
                        </div>
                        <a href="{{ route('officer.cases.index') }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-800">View all</a>
                    </div>

                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-normal text-gray-500">
                                    <th scope="col" class="py-3 pr-4">Reference</th>
                                    <th scope="col" class="px-4 py-3">Type</th>
                                    <th scope="col" class="px-4 py-3">Assignment</th>
                                    <th scope="col" class="px-4 py-3">Urgency</th>
                                    <th scope="col" class="px-4 py-3">Status</th>
                                    <th scope="col" class="py-3 pl-4">Updated</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($recentlyUpdatedCases as $case)
                                    <tr>
                                        <td class="whitespace-nowrap py-3 pr-4">
                                            <a href="{{ route('officer.cases.show', $case) }}" class="font-semibold text-indigo-700 hover:text-indigo-800">{{ $case->case_reference }}</a>
                                            @if ($case->unread_messages_count > 0)
                                                <span class="ml-2 inline-flex rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200">{{ $case->unread_messages_count }}</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $case->abuseType?->name ?? 'Unspecified' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $case->assignedOfficer?->name ?? 'Open queue' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ str($case->urgency)->headline() }}</td>
                                        <td class="whitespace-nowrap px-4 py-3"><x-status-badge :status="$case->status" /></td>
                                        <td class="whitespace-nowrap py-3 pl-4 text-gray-600">{{ $case->updated_at->format('M j, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-sm text-gray-500">No cases are currently visible in your queue.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Recent Assignments</h3>
                        <p class="mt-1 text-sm text-gray-600">Quick access to cases assigned to you.</p>
                    </div>
                    <a href="{{ route('officer.cases.index', ['status' => \App\Models\SupportCase::STATUS_ASSIGNED]) }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-800">Assigned filter</a>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                    @forelse ($recentAssignments as $case)
                        <article class="rounded-lg border border-gray-200 p-4">
                            <a href="{{ route('officer.cases.show', $case) }}" class="font-semibold text-indigo-700 hover:text-indigo-800">{{ $case->case_reference }}</a>
                            <p class="mt-2 text-sm text-gray-600">{{ $case->abuseType?->name ?? 'Unspecified' }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <x-status-badge :status="$case->status" />
                                <span class="text-xs font-medium text-gray-500">{{ $case->updated_at->format('M j, Y') }}</span>
                            </div>
                        </article>
                    @empty
                        <p class="rounded-md bg-gray-50 p-4 text-sm text-gray-600 md:col-span-2 xl:col-span-5">No cases have been assigned to you yet.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
