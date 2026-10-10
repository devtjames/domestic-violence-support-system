<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-700">Administration area</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Admin Dashboard') }}
                </h2>
            </div>
            <span class="inline-flex w-fit rounded-md bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-700 ring-1 ring-emerald-200">
                {{ ucfirst(Auth::user()->account_status) }}
            </span>
        </div>
    </x-slot>

    @php
        $cardClasses = [
            'rose' => 'bg-rose-50 text-rose-800 ring-rose-100',
            'indigo' => 'bg-indigo-50 text-indigo-800 ring-indigo-100',
            'slate' => 'bg-slate-50 text-slate-800 ring-slate-100',
            'amber' => 'bg-amber-50 text-amber-800 ring-amber-100',
            'sky' => 'bg-sky-50 text-sky-800 ring-sky-100',
            'cyan' => 'bg-cyan-50 text-cyan-800 ring-cyan-100',
            'emerald' => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
            'violet' => 'bg-violet-50 text-violet-800 ring-violet-100',
            'teal' => 'bg-teal-50 text-teal-800 ring-teal-100',
            'red' => 'bg-red-50 text-red-800 ring-red-100',
            'fuchsia' => 'bg-fuchsia-50 text-fuchsia-800 ring-fuchsia-100',
            'lime' => 'bg-lime-50 text-lime-800 ring-lime-100',
        ];

        $chartPalette = ['#be123c', '#0f766e', '#4f46e5', '#ca8a04', '#0284c7', '#dc2626', '#7c3aed', '#15803d'];

        $casesByMonthChart = [
            'type' => 'line',
            'data' => [
                'labels' => array_keys($casesByMonth),
                'datasets' => [[
                    'label' => 'Cases',
                    'data' => array_values($casesByMonth),
                    'borderColor' => '#be123c',
                    'backgroundColor' => 'rgba(190, 18, 60, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ]],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => ['legend' => ['display' => false]],
                'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
            ],
        ];

        $caseStatusChart = [
            'type' => 'bar',
            'data' => [
                'labels' => array_keys($caseStatusCounts),
                'datasets' => [[
                    'label' => 'Cases',
                    'data' => array_values($caseStatusCounts),
                    'backgroundColor' => $chartPalette,
                    'borderRadius' => 4,
                ]],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => ['legend' => ['display' => false]],
                'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
            ],
        ];

        $caseUrgencyChart = [
            'type' => 'doughnut',
            'data' => [
                'labels' => array_keys($caseUrgencyCounts),
                'datasets' => [[
                    'data' => array_values($caseUrgencyCounts),
                    'backgroundColor' => ['#15803d', '#ca8a04', '#dc2626', '#7f1d1d'],
                ]],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => ['legend' => ['position' => 'bottom']],
            ],
        ];

        $caseSourceChart = [
            'type' => 'doughnut',
            'data' => [
                'labels' => array_keys($caseSourceCounts),
                'datasets' => [[
                    'data' => array_values($caseSourceCounts),
                    'backgroundColor' => ['#0f766e', '#7c3aed'],
                ]],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => ['legend' => ['position' => 'bottom']],
            ],
        ];

        $caseTypeChart = [
            'type' => 'bar',
            'data' => [
                'labels' => array_keys($caseTypeCounts),
                'datasets' => [[
                    'label' => 'Cases',
                    'data' => array_values($caseTypeCounts),
                    'backgroundColor' => '#0f766e',
                    'borderRadius' => 4,
                ]],
            ],
            'options' => [
                'indexAxis' => 'y',
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => ['legend' => ['display' => false]],
                'scales' => ['x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
            ],
        ];
    @endphp

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="metric-grid grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($summaryCards as $card)
                    <article class="metric-card rounded-lg p-5 ring-1 {{ $cardClasses[$card['tone']] ?? $cardClasses['slate'] }}">
                        <p class="text-sm font-medium">{{ $card['label'] }}</p>
                        <p class="mt-3 text-3xl font-semibold tracking-normal">{{ number_format($card['value']) }}</p>
                    </article>
                @endforeach
            </section>

            <section class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg lg:col-span-2">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase text-rose-700">System overview</p>
                            <h3 class="mt-2 text-xl font-semibold text-gray-900">Admin reporting snapshot</h3>
                            <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-600">
                                Dashboard widgets use counts, categories, case references, and activity labels only. They do not show case descriptions, private messages, internal notes, or anonymous contact details.
                            </p>
                        </div>
                        <a href="{{ route('admin.cases.index') }}" class="inline-flex w-fit items-center rounded-md bg-rose-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-600 focus:ring-offset-2">
                            Manage cases
                        </a>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-base font-semibold text-gray-900">Quick administration</h3>
                    <div class="mt-4 grid gap-3 text-sm">
                        <a href="{{ route('admin.users.index') }}" class="font-medium text-rose-700 hover:text-rose-800">Registered users</a>
                        <a href="{{ route('admin.support-officers.index') }}" class="font-medium text-rose-700 hover:text-rose-800">Support officers</a>
                        <a href="{{ route('admin.awareness.articles.index') }}" class="font-medium text-rose-700 hover:text-rose-800">Awareness articles</a>
                        <a href="{{ route('admin.awareness.categories.index') }}" class="font-medium text-rose-700 hover:text-rose-800">Awareness categories</a>
                        <a href="{{ route('admin.resources.index') }}" class="font-medium text-rose-700 hover:text-rose-800">Support resources</a>
                        <a href="{{ route('admin.faqs.index') }}" class="font-medium text-rose-700 hover:text-rose-800">FAQs</a>
                        <a href="{{ route('admin.contact-messages.index') }}" class="font-medium text-rose-700 hover:text-rose-800">Contact messages</a>
                        <a href="{{ route('admin.reports.index') }}" class="font-medium text-rose-700 hover:text-rose-800">Reports</a>
                        <a href="{{ route('admin.audit-logs.index') }}" class="font-medium text-rose-700 hover:text-rose-800">Audit logs</a>
                        <a href="{{ route('admin.settings.edit') }}" class="font-medium text-rose-700 hover:text-rose-800">System settings</a>
                        <a href="{{ route('profile.edit') }}" class="font-medium text-rose-700 hover:text-rose-800">Profile settings</a>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-2">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Cases by Month</h3>
                            <p class="mt-1 text-sm text-gray-600">Support requests created in the last six months.</p>
                        </div>
                    </div>
                    <div class="mt-6 h-72">
                        <canvas data-dashboard-chart data-chart-source="cases-by-month-chart" aria-label="Cases by month chart" role="img"></canvas>
                    </div>
                    <script type="application/json" id="cases-by-month-chart">@json($casesByMonthChart)</script>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-base font-semibold text-gray-900">Cases by Status</h3>
                    <p class="mt-1 text-sm text-gray-600">Current case workload grouped by workflow status.</p>
                    <div class="mt-6 h-72">
                        <canvas data-dashboard-chart data-chart-source="cases-by-status-chart" aria-label="Cases by status chart" role="img"></canvas>
                    </div>
                    <script type="application/json" id="cases-by-status-chart">@json($caseStatusChart)</script>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-base font-semibold text-gray-900">Cases by Urgency</h3>
                    <p class="mt-1 text-sm text-gray-600">Priority distribution for active monitoring.</p>
                    <div class="mt-6 h-72">
                        <canvas data-dashboard-chart data-chart-source="cases-by-urgency-chart" aria-label="Cases by urgency chart" role="img"></canvas>
                    </div>
                    <script type="application/json" id="cases-by-urgency-chart">@json($caseUrgencyChart)</script>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-base font-semibold text-gray-900">Anonymous vs Registered Cases</h3>
                    <p class="mt-1 text-sm text-gray-600">Submission source without displaying personal details.</p>
                    <div class="mt-6 h-72">
                        <canvas data-dashboard-chart data-chart-source="cases-by-source-chart" aria-label="Anonymous vs registered cases chart" role="img"></canvas>
                    </div>
                    <script type="application/json" id="cases-by-source-chart">@json($caseSourceChart)</script>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg xl:col-span-2">
                    <h3 class="text-base font-semibold text-gray-900">Cases by Abuse Type</h3>
                    <p class="mt-1 text-sm text-gray-600">Category totals for reporting and planning.</p>
                    <div class="mt-6 h-96">
                        <canvas data-dashboard-chart data-chart-source="cases-by-type-chart" aria-label="Cases by abuse type chart" role="img"></canvas>
                    </div>
                    <script type="application/json" id="cases-by-type-chart">@json($caseTypeChart)</script>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-5">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg xl:col-span-3">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Recent Cases</h3>
                            <p class="mt-1 text-sm text-gray-600">References and workflow metadata only.</p>
                        </div>
                        <a href="{{ route('admin.cases.index') }}" class="text-sm font-semibold text-rose-700 hover:text-rose-800">View all</a>
                    </div>

                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-normal text-gray-500">
                                    <th scope="col" class="py-3 pr-4">Reference</th>
                                    <th scope="col" class="px-4 py-3">Type</th>
                                    <th scope="col" class="px-4 py-3">Source</th>
                                    <th scope="col" class="px-4 py-3">Urgency</th>
                                    <th scope="col" class="px-4 py-3">Status</th>
                                    <th scope="col" class="px-4 py-3">Officer</th>
                                    <th scope="col" class="py-3 pl-4">Updated</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($recentCases as $case)
                                    <tr>
                                        <td class="whitespace-nowrap py-3 pr-4 font-semibold text-rose-700">
                                            <a href="{{ route('admin.cases.show', $case) }}" class="hover:text-rose-800">{{ $case->case_reference }}</a>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $case->abuseType?->name ?? 'Unspecified' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $case->is_anonymous ? 'Anonymous' : 'Registered' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ str($case->urgency)->headline() }}</td>
                                        <td class="whitespace-nowrap px-4 py-3">
                                            <x-status-badge :status="$case->status" />
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $case->assignedOfficer?->name ?? 'Unassigned' }}</td>
                                        <td class="whitespace-nowrap py-3 pl-4 text-gray-600">{{ $case->updated_at->format('M j, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-sm text-gray-500">No support cases have been submitted yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg xl:col-span-2">
                    <h3 class="text-base font-semibold text-gray-900">Recent Activity</h3>
                    <p class="mt-1 text-sm text-gray-600">Audit-friendly labels without private note or message content.</p>

                    <div class="mt-5 space-y-4">
                        @forelse ($recentActivities as $activity)
                            <div class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $activity->label() }}</p>
                                        <p class="mt-1 text-sm text-gray-600">
                                            Case
                                            @if ($activity->supportCase)
                                                <a href="{{ route('admin.cases.show', $activity->supportCase) }}" class="font-medium text-rose-700 hover:text-rose-800">{{ $activity->supportCase->case_reference }}</a>
                                            @else
                                                unavailable
                                            @endif
                                        </p>
                                    </div>
                                    <time datetime="{{ $activity->created_at->toIso8601String() }}" class="shrink-0 text-xs text-gray-500">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </time>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">
                                    Actor: {{ $activity->actor?->name ?? 'System' }}
                                </p>
                            </div>
                        @empty
                            <p class="rounded-md bg-gray-50 p-4 text-sm text-gray-600">No case activity has been recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
