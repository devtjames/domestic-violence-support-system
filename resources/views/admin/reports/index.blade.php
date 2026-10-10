<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-700">Reports and statistics</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Case Reports</h2>
            </div>
            <div class="flex flex-wrap gap-2 print:hidden">
                <button type="button" onclick="window.print()" class="inline-flex w-fit justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Print Report</button>
                <a href="{{ route('admin.reports.export', request()->query()) }}" class="inline-flex w-fit justify-center rounded-md bg-rose-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-800">Export CSV</a>
            </div>
        </div>
    </x-slot>

    @php
        $cardClasses = [
            'slate' => 'bg-slate-50 text-slate-800 ring-slate-100',
            'amber' => 'bg-amber-50 text-amber-800 ring-amber-100',
            'emerald' => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
            'violet' => 'bg-violet-50 text-violet-800 ring-violet-100',
            'teal' => 'bg-teal-50 text-teal-800 ring-teal-100',
            'red' => 'bg-red-50 text-red-800 ring-red-100',
        ];

        $groupTitles = [
            'date' => 'Cases by Date',
            'status' => 'Cases by Status',
            'abuseType' => 'Cases by Abuse Type',
            'urgency' => 'Cases by Urgency',
            'officer' => 'Cases by Support Officer',
            'source' => 'Anonymous vs Registered',
            'workflow' => 'Pending vs Resolved',
        ];
    @endphp

    <style>
        @media print {
            nav, header, .print\:hidden {
                display: none !important;
            }

            body {
                background: #fff !important;
            }
        }
    </style>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @include('admin.partials.feedback')

            <section class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">
                Reports show statistical and workflow information only. Case descriptions, private messages, internal notes, anonymous contact details, and safe contact instructions are excluded from this page and CSV exports.
            </section>

            <form method="GET" action="{{ route('admin.reports.index') }}" class="grid gap-4 rounded-md border border-gray-200 bg-white p-5 shadow-sm print:hidden lg:grid-cols-4">
                <div>
                    <x-input-label for="date_from" value="Date From" />
                    <x-text-input id="date_from" name="date_from" type="date" :value="request('date_from')" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('date_from')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="date_to" value="Date To" />
                    <x-text-input id="date_to" name="date_to" type="date" :value="request('date_to')" class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('date_to')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="status" value="Status" />
                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->headline() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="urgency" value="Urgency" />
                    <select id="urgency" name="urgency" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All urgency levels</option>
                        @foreach ($urgencies as $urgency)
                            <option value="{{ $urgency }}" @selected(request('urgency') === $urgency)>{{ str($urgency)->headline() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('urgency')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="abuse_type_id" value="Abuse Type" />
                    <select id="abuse_type_id" name="abuse_type_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All abuse types</option>
                        @foreach ($abuseTypes as $abuseType)
                            <option value="{{ $abuseType->id }}" @selected((string) request('abuse_type_id') === (string) $abuseType->id)>{{ $abuseType->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('abuse_type_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="assigned_officer_id" value="Support Officer" />
                    <select id="assigned_officer_id" name="assigned_officer_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All officers</option>
                        <option value="unassigned" @selected(request('assigned_officer_id') === 'unassigned')>Unassigned</option>
                        @foreach ($officers as $officer)
                            <option value="{{ $officer->id }}" @selected((string) request('assigned_officer_id') === (string) $officer->id)>
                                {{ $officer->name }}{{ $officer->account_status === \App\Models\User::STATUS_DISABLED ? ' (disabled)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('assigned_officer_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="source" value="Source" />
                    <select id="source" name="source" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All sources</option>
                        <option value="registered" @selected(request('source') === 'registered')>Registered</option>
                        <option value="anonymous" @selected(request('source') === 'anonymous')>Anonymous</option>
                    </select>
                    <x-input-error :messages="$errors->get('source')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="workflow" value="Workflow" />
                    <select id="workflow" name="workflow" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All workflows</option>
                        <option value="pending" @selected(request('workflow') === 'pending')>Pending</option>
                        <option value="resolved" @selected(request('workflow') === 'resolved')>Resolved</option>
                    </select>
                    <x-input-error :messages="$errors->get('workflow')" class="mt-2" />
                </div>

                <div class="flex items-end gap-3 lg:col-span-4">
                    <x-primary-button>Apply Filters</x-primary-button>
                    <a href="{{ route('admin.reports.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</a>
                </div>
            </form>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
                @foreach ($summaryCards as $card)
                    <article class="rounded-lg p-5 ring-1 {{ $cardClasses[$card['tone']] ?? $cardClasses['slate'] }}">
                        <p class="text-sm font-medium">{{ $card['label'] }}</p>
                        <p class="mt-3 text-3xl font-semibold tracking-normal">{{ number_format($card['value']) }}</p>
                    </article>
                @endforeach
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                @foreach ($groupedCounts as $key => $counts)
                    <article class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 class="text-base font-semibold text-gray-900">{{ $groupTitles[$key] ?? str($key)->headline() }}</h3>
                        <dl class="mt-5 space-y-3 text-sm">
                            @forelse ($counts as $label => $count)
                                <div class="flex items-center justify-between gap-4 border-b border-gray-100 pb-3 last:border-b-0 last:pb-0">
                                    <dt class="text-gray-600">{{ $label }}</dt>
                                    <dd class="font-semibold text-gray-900">{{ number_format($count) }}</dd>
                                </div>
                            @empty
                                <div class="rounded-md bg-gray-50 p-4 text-gray-600">No records for this grouping.</div>
                            @endforelse
                        </dl>
                    </article>
                @endforeach
            </section>

            <section class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-gray-200 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Filtered Case Records</h3>
                        <p class="mt-1 text-sm text-gray-600">Safe reporting fields only.</p>
                    </div>
                    <span class="text-sm text-gray-500">{{ number_format($cases->total()) }} matching records</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left font-semibold text-gray-700">
                            <tr>
                                <th class="px-4 py-3">Reference</th>
                                <th class="px-4 py-3">Submitted</th>
                                <th class="px-4 py-3">Source</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Urgency</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Officer</th>
                                <th class="px-4 py-3">Resolved</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($cases as $case)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-4 font-semibold text-rose-700">
                                        <a href="{{ route('admin.cases.show', $case) }}" class="hover:text-rose-800">{{ $case->case_reference }}</a>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-gray-700">{{ $case->created_at->format('M j, Y') }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-gray-700">{{ $case->is_anonymous ? 'Anonymous' : 'Registered' }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-gray-700">{{ $case->abuseType?->name ?? 'Unspecified' }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-gray-700">{{ str($case->urgency)->headline() }}</td>
                                    <td class="whitespace-nowrap px-4 py-4"><x-status-badge :status="$case->status" /></td>
                                    <td class="whitespace-nowrap px-4 py-4 text-gray-700">{{ $case->assignedOfficer?->name ?? 'Unassigned' }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-gray-700">{{ $case->resolved_at?->format('M j, Y') ?? 'Not resolved' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-600">No cases match these report filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="print:hidden">{{ $cases->links() }}</div>
        </div>
    </div>
</x-app-layout>
