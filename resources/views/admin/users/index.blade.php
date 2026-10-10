<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-700">User management</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Registered Users</h2>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex w-fit justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Dashboard</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.feedback')

            <form method="GET" action="{{ route('admin.users.index') }}" class="grid gap-3 rounded-md border border-gray-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_180px_180px_auto]">
                <x-text-input name="search" :value="request('search')" placeholder="Search name or email" class="w-full" />
                <select name="account_status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    @foreach ($accountStatuses as $status)
                        <option value="{{ $status }}" @selected(request('account_status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <select name="sort" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach ($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('sort', 'newest') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="flex gap-3">
                    <x-primary-button>Filter</x-primary-button>
                    <a href="{{ route('admin.users.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</a>
                </div>
            </form>

            <div class="mt-6 overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left font-semibold text-gray-700">
                            <tr>
                                <th class="px-4 py-3">User</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Cases</th>
                                <th class="px-4 py-3">Joined</th>
                                <th class="px-4 py-3">Verified</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($users as $managedUser)
                                <tr>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">{{ $managedUser->name }}</div>
                                        <div class="mt-1 text-xs text-gray-500">{{ $managedUser->email }}</div>
                                    </td>
                                    <td class="px-4 py-4"><x-status-badge :status="$managedUser->account_status" /></td>
                                    <td class="px-4 py-4 text-gray-700">{{ $managedUser->support_cases_count }}</td>
                                    <td class="px-4 py-4 text-gray-700">{{ $managedUser->created_at->format('M j, Y') }}</td>
                                    <td class="px-4 py-4 text-gray-700">{{ $managedUser->email_verified_at ? $managedUser->email_verified_at->format('M j, Y') : 'Unverified' }}</td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('admin.users.show', $managedUser) }}" class="rounded-md border border-gray-300 px-3 py-2 font-medium text-gray-700 hover:bg-gray-50">View</a>
                                            @if ($managedUser->account_status === \App\Models\User::STATUS_ACTIVE)
                                                <form method="POST" action="{{ route('admin.users.deactivate', $managedUser) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="rounded-md border border-amber-200 px-3 py-2 font-medium text-amber-700 hover:bg-amber-50">Deactivate</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.users.activate', $managedUser) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="rounded-md border border-emerald-200 px-3 py-2 font-medium text-emerald-700 hover:bg-emerald-50">Activate</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-600">No registered users match the current filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">{{ $users->links() }}</div>
        </div>
    </div>
</x-app-layout>
