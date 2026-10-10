<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-700">Support resource management</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Resources</h2>
            </div>
            <a href="{{ route('admin.resources.create') }}" class="inline-flex w-fit justify-center rounded-md bg-rose-700 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-800">New Resource</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.feedback')

            <form method="GET" action="{{ route('admin.resources.index') }}" class="grid gap-3 rounded-md border border-gray-200 bg-white p-4 shadow-sm lg:grid-cols-[1fr_220px_180px_160px_180px_auto]">
                <x-text-input name="search" :value="request('search')" placeholder="Search organization or contact" class="w-full" />
                <select name="resource_type" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All types</option>
                    @foreach ($resourceTypes as $type)
                        <option value="{{ $type }}" @selected(request('resource_type') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
                <x-text-input name="location" :value="request('location')" placeholder="Location" class="w-full" />
                <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <select name="sort" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach ($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('sort', 'organization') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="flex gap-3">
                    <x-primary-button>Filter</x-primary-button>
                    <a href="{{ route('admin.resources.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</a>
                </div>
            </form>

            <div class="mt-6 overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left font-semibold text-gray-700">
                            <tr>
                                <th class="px-4 py-3">Organization</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Location</th>
                                <th class="px-4 py-3">Availability</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($resources as $resource)
                                <tr>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">{{ $resource->organization_name }}</div>
                                        <div class="mt-1 max-w-md text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($resource->description, 110) }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-gray-700">{{ $resource->resource_type }}</td>
                                    <td class="px-4 py-4 text-gray-700">{{ $resource->location }}</td>
                                    <td class="px-4 py-4 text-gray-700">{{ $resource->availability }}</td>
                                    <td class="px-4 py-4"><x-status-badge :status="$resource->status" /></td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('admin.resources.show', $resource) }}" class="rounded-md border border-gray-300 px-3 py-2 font-medium text-gray-700 hover:bg-gray-50">View</a>
                                            <a href="{{ route('admin.resources.edit', $resource) }}" class="rounded-md border border-gray-300 px-3 py-2 font-medium text-gray-700 hover:bg-gray-50">Edit</a>
                                            <form method="POST" action="{{ route('admin.resources.destroy', $resource) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-md border border-rose-200 px-3 py-2 font-medium text-rose-700 hover:bg-rose-50">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-600">No support resources match the current filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">{{ $resources->links() }}</div>
        </div>
    </div>
</x-app-layout>
