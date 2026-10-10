<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-700">FAQ management</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit FAQ Category</h2>
            </div>
            <a href="{{ route('admin.faq-categories.index') }}" class="inline-flex w-fit justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">All Categories</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.feedback')

            @include('admin.faqs.categories.partials.form', [
                'action' => route('admin.faq-categories.update', $category),
                'method' => 'PATCH',
                'buttonText' => 'Save Category',
            ])
        </div>
    </div>
</x-app-layout>
