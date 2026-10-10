<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-700">FAQ management</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">FAQ Categories</h2>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('admin.faqs.index') }}" class="inline-flex w-fit justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">FAQs</a>
                <a href="{{ route('admin.faq-categories.create') }}" class="inline-flex w-fit justify-center rounded-md bg-rose-700 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-800">New Category</a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.feedback')

            <form method="GET" action="{{ route('admin.faq-categories.index') }}" class="grid gap-3 rounded-md border border-gray-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_180px_auto]">
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
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3">FAQs</th>
                                <th class="px-4 py-3">Order</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($categories as $category)
                                <tr>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-gray-900">{{ $category->name }}</div>
                                        <div class="mt-1 max-w-xl text-xs text-gray-500">{{ $category->description ?: 'No description provided.' }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-gray-700">{{ $category->faqs_count }}</td>
                                    <td class="px-4 py-4 text-gray-700">{{ $category->sort_order }}</td>
                                    <td class="px-4 py-4"><x-status-badge :status="$category->status" /></td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <form method="POST" action="{{ route('admin.faq-categories.move-up', $category) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-md border border-gray-300 px-3 py-2 font-medium text-gray-700 hover:bg-gray-50">Up</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.faq-categories.move-down', $category) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-md border border-gray-300 px-3 py-2 font-medium text-gray-700 hover:bg-gray-50">Down</button>
                                            </form>
                                            @if ($category->status === \App\Models\FaqCategory::STATUS_ACTIVE)
                                                <form method="POST" action="{{ route('admin.faq-categories.deactivate', $category) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="rounded-md border border-amber-200 px-3 py-2 font-medium text-amber-700 hover:bg-amber-50">Deactivate</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.faq-categories.activate', $category) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="rounded-md border border-emerald-200 px-3 py-2 font-medium text-emerald-700 hover:bg-emerald-50">Activate</button>
                                                </form>
                                            @endif
                                            <a href="{{ route('admin.faq-categories.edit', $category) }}" class="rounded-md border border-gray-300 px-3 py-2 font-medium text-gray-700 hover:bg-gray-50">Edit</a>
                                            <form method="POST" action="{{ route('admin.faq-categories.destroy', $category) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-md border border-rose-200 px-3 py-2 font-medium text-rose-700 hover:bg-rose-50">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-600">No FAQ categories found.</td>
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
