<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-700">Awareness management</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Categories</h2>
            </div>
            <a href="{{ route('admin.awareness.categories.create') }}" class="inline-flex w-fit rounded-md bg-rose-700 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-800">New Category</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @include('admin.awareness.partials.feedback')

            <form method="GET" action="{{ route('admin.awareness.categories.index') }}" class="grid gap-3 rounded-md border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_180px_auto]">
                <x-text-input name="search" :value="request('search')" placeholder="Search categories" class="w-full" />
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
                                <th class="px-4 py-3">Articles</th>
                                <th class="px-4 py-3">Sort</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($categories as $category)
                                <tr>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">{{ $category->name }}</div>
                                        <div class="mt-1 text-xs text-gray-500">{{ $category->slug }}</div>
                                    </td>
                                    <td class="px-4 py-4"><x-status-badge :status="$category->status" /></td>
                                    <td class="px-4 py-4 text-gray-700">{{ $category->articles_count }}</td>
                                    <td class="px-4 py-4 text-gray-700">{{ $category->sort_order }}</td>
                                    <td class="px-4 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.awareness.categories.edit', $category) }}" class="rounded-md border border-gray-300 px-3 py-2 font-medium text-gray-700 hover:bg-gray-50">Edit</a>
                                            <form method="POST" action="{{ route('admin.awareness.categories.destroy', $category) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-md border border-rose-200 px-3 py-2 font-medium text-rose-700 hover:bg-rose-50">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-600">No awareness categories found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">{{ $categories->links() }}</div>
        </div>
    </div>
</x-app-layout>
