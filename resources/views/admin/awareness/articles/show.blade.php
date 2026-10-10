<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-700">Article preview</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $article->title }}</h2>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('admin.awareness.articles.edit', $article) }}" class="inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Edit</a>
                @if ($article->status !== 'published')
                    <form method="POST" action="{{ route('admin.awareness.articles.publish', $article) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Publish</button>
                    </form>
                @endif
                @if ($article->status !== 'archived')
                    <form method="POST" action="{{ route('admin.awareness.articles.archive', $article) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="rounded-md bg-rose-700 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-800">Archive</button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @include('admin.awareness.partials.feedback')

            <article class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
                @if ($article->featured_image)
                    <img src="{{ Storage::url($article->featured_image) }}" alt="" class="h-64 w-full object-cover">
                @endif
                <div class="p-6 sm:p-8">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-status-badge :status="$article->status" />
                        <span class="text-sm text-gray-600">{{ $article->category->name }}</span>
                        <span class="text-sm text-gray-600">By {{ $article->author->name }}</span>
                    </div>
                    <h1 class="mt-5 text-3xl font-semibold text-gray-900">{{ $article->title }}</h1>
                    <p class="mt-4 text-base leading-7 text-gray-600">{{ $article->short_description }}</p>
                    <div class="mt-8 whitespace-pre-line text-base leading-8 text-gray-700">{{ $article->content }}</div>
                </div>
            </article>
        </div>
    </div>
</x-app-layout>
