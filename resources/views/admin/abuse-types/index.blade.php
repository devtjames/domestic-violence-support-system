<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-700">Administration area</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Abuse Types</h2>
            </div>
            <a href="{{ route('admin.abuse-types.create') }}" class="inline-flex w-fit justify-center rounded-md bg-rose-700 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-800">New Abuse Type</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.feedback')

            <form method="GET" action="{{ route('admin.abuse-types.index') }}" class="grid gap-3 rounded-md border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_180px_auto]">
                <x-text-input name="search" :value="request('search')" placeholder="Search abuse types" class="w-full" />
                <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <x-primary-button>Filter</x-primary-button>
            </form>

            <div class="mt-6 overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left font-semibold text-gray-700">
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Cases</th>
                                <th class="px-4 py-3">Sort</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($abuseTypes as $abuseType)
                                <tr>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">{{ $abuseType->name }}</div>
                                        <div class="mt-1 text-xs text-gray-500">{{ $abuseType->slug }}</div>
                                        @if ($abuseType->description)
                                            <div class="mt-1 max-w-xl text-xs leading-5 text-gray-500">{{ $abuseType->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4"><x-status-badge :status="$abuseType->status" /></td>
                                    <td class="px-4 py-4 text-gray-700">{{ $abuseType->support_cases_count }}</td>
                                    <td class="px-4 py-4 text-gray-700">{{ $abuseType->sort_order }}</td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('admin.abuse-types.edit', $abuseType) }}" class="rounded-md border border-gray-300 px-3 py-2 font-medium text-gray-700 hover:bg-gray-50">Edit</a>
                                            @if ($abuseType->status === \App\Models\AbuseType::STATUS_ACTIVE)
                                                <form method="POST" action="{{ route('admin.abuse-types.deactivate', $abuseType) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="rounded-md border border-amber-200 px-3 py-2 font-medium text-amber-700 hover:bg-amber-50">Deactivate</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.abuse-types.activate', $abuseType) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="rounded-md border border-emerald-200 px-3 py-2 font-medium text-emerald-700 hover:bg-emerald-50">Activate</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.abuse-types.destroy', $abuseType) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-md border border-rose-200 px-3 py-2 font-medium text-rose-700 hover:bg-rose-50">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-600">No abuse types found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">{{ $abuseTypes->links() }}</div>
        </div>
    </div>
</x-app-layout>
