<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Awareness Article</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @include('admin.awareness.partials.feedback')
            @include('admin.awareness.articles.partials.form', [
                'action' => route('admin.awareness.articles.store'),
                'method' => 'POST',
                'buttonText' => 'Create Article',
            ])
        </div>
    </div>
</x-app-layout>
