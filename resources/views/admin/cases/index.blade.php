<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-700">Case management</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">All Support Cases</h2>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex w-fit justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Dashboard</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.feedback')

            <form method="GET" action="{{ route('admin.cases.index') }}" class="grid gap-3 rounded-md border border-gray-200 bg-white p-4 shadow-sm lg:grid-cols-4 xl:grid-cols-[1fr_160px_145px_180px_180px_145px]">
                <x-text-input name="search" :value="request('search')" placeholder="Search reference, subject, user, or email" class="w-full" />
                <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                    @endforeach
                </select>
                <select name="urgency" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All urgencies</option>
                    @foreach ($urgencies as $urgency)
                        <option value="{{ $urgency }}" @selected(request('urgency') === $urgency)>{{ \Illuminate\Support\Str::headline($urgency) }}</option>
                    @endforeach
                </select>
                <select name="abuse_type" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All types</option>
                    @foreach ($abuseTypes as $abuseType)
                        <option value="{{ $abuseType->id }}" @selected((string) request('abuse_type') === (string) $abuseType->id)>{{ $abuseType->name }}</option>
                    @endforeach
                </select>
                <select name="assigned_officer" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All officers</option>
                    <option value="unassigned" @selected(request('assigned_officer') === 'unassigned')>Unassigned</option>
                    @foreach ($officers as $officer)
                        <option value="{{ $officer->id }}" @selected((string) request('assigned_officer') === (string) $officer->id)>{{ $officer->name }}</option>
                    @endforeach
                </select>
                <select name="source" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All sources</option>
                    <option value="registered" @selected(request('source') === 'registered')>Registered</option>
                    <option value="anonymous" @selected(request('source') === 'anonymous')>Anonymous</option>
                </select>
                <x-text-input name="date_from" type="date" :value="request('date_from')" class="w-full" />
                <x-text-input name="date_to" type="date" :value="request('date_to')" class="w-full" />
                <select name="sort" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach ($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('sort', 'newest') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="flex gap-3 xl:col-span-3">
                    <x-primary-button>Filter</x-primary-button>
                    <a href="{{ route('admin.cases.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</a>
                </div>
            </form>

            <div class="mt-6 overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left font-semibold text-gray-700">
                            <tr>
                                <th class="px-4 py-3">Case</th>
                                <th class="px-4 py-3">Source</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Urgency</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Officer</th>
                                <th class="px-4 py-3">Submitted</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($cases as $case)
                                <tr>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">{{ $case->case_reference }}</div>
                                        <div class="mt-1 max-w-md text-xs text-gray-500">{{ $case->subject }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-gray-700">{{ $case->is_anonymous ? 'Anonymous' : 'Registered' }}</td>
                                    <td class="px-4 py-4 text-gray-700">{{ $case->abuseType->name }}</td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold capitalize ring-1 {{ $case->urgency === 'critical' ? 'bg-rose-50 text-rose-700 ring-rose-200' : ($case->urgency === 'high' ? 'bg-amber-50 text-amber-700 ring-amber-200' : 'bg-gray-100 text-gray-700 ring-gray-200') }}">
                                            {{ \Illuminate\Support\Str::headline($case->urgency) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4"><x-status-badge :status="$case->status" /></td>
                                    <td class="px-4 py-4 text-gray-700">{{ $case->assignedOfficer?->name ?? 'Unassigned' }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-gray-700">{{ $case->created_at->format('M j, Y') }}</td>
                                    <td class="px-4 py-4 text-right">
                                        <a href="{{ route('admin.cases.show', $case) }}" class="rounded-md border border-gray-300 px-3 py-2 font-medium text-gray-700 hover:bg-gray-50">Manage</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-600">No support cases match the current filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">{{ $cases->links() }}</div>
        </div>
    </div>
</x-app-layout>
