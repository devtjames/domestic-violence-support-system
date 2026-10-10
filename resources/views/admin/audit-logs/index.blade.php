<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-700">Accountability records</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Audit Logs</h2>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex w-fit justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Dashboard</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('admin.partials.feedback')

            <section class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">
                Audit records are read-only. They track administrative and support actions without storing passwords, private message text, internal note content, case descriptions, or anonymous access codes.
            </section>

            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid gap-3 rounded-md border border-gray-200 bg-white p-4 shadow-sm lg:grid-cols-6">
                <x-text-input name="search" :value="request('search')" placeholder="Search action or description" class="w-full lg:col-span-2" />

                <select name="module" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All modules</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module }}" @selected(request('module') === $module)>{{ str($module)->headline() }}</option>
                    @endforeach
                </select>

                <select name="actor" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All actors</option>
                    @foreach ($actors as $actor)
                        <option value="{{ $actor->id }}" @selected((string) request('actor') === (string) $actor->id)>{{ $actor->name }} ({{ str($actor->role)->headline() }})</option>
                    @endforeach
                </select>

                <x-text-input name="date_from" type="date" :value="request('date_from')" class="w-full" />
                <x-text-input name="date_to" type="date" :value="request('date_to')" class="w-full" />

                <x-text-input name="action" :value="request('action')" placeholder="Action" class="w-full lg:col-span-2" />

                <select name="sort" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach ($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('sort', 'newest') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <div class="flex gap-3 lg:col-span-3">
                    <x-primary-button>Filter</x-primary-button>
                    <a href="{{ route('admin.audit-logs.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</a>
                </div>
            </form>

            <section class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left font-semibold text-gray-700">
                            <tr>
                                <th class="px-4 py-3">Time</th>
                                <th class="px-4 py-3">Actor</th>
                                <th class="px-4 py-3">Module</th>
                                <th class="px-4 py-3">Action</th>
                                <th class="px-4 py-3">Description</th>
                                <th class="px-4 py-3 text-right">View</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-4 text-gray-700">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">{{ $log->actor?->name ?? 'System' }}</div>
                                        <div class="mt-1 text-xs text-gray-500">{{ $log->actor?->role ? str($log->actor->role)->headline() : 'No actor' }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-gray-700">{{ str($log->module)->headline() }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-gray-700">{{ str($log->action)->headline() }}</td>
                                    <td class="px-4 py-4 text-gray-700">{{ $log->description }}</td>
                                    <td class="px-4 py-4 text-right">
                                        <a href="{{ route('admin.audit-logs.show', $log) }}" class="font-semibold text-rose-700 hover:text-rose-800">Details</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-600">No audit logs match these filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div>{{ $logs->links() }}</div>
        </div>
    </div>
</x-app-layout>
