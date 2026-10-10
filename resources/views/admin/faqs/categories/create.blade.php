<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-medium text-rose-700">FAQ management</p>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create FAQ Category</h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.feedback')

            @include('admin.faqs.categories.partials.form', [
                'action' => route('admin.faq-categories.store'),
                'method' => 'POST',
                'buttonText' => 'Create Category',
            ])
        </div>
    </div>
</x-app-layout>
