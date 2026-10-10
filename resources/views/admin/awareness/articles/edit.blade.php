<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Awareness Article</h2>
            <a href="{{ route('admin.awareness.articles.show', $article) }}" class="inline-flex w-fit rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Preview</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @include('admin.awareness.partials.feedback')
            @include('admin.awareness.articles.partials.form', [
                'action' => route('admin.awareness.articles.update', $article),
                'method' => 'PATCH',
                'buttonText' => 'Save Changes',
            ])
        </div>
    </div>
</x-app-layout>
