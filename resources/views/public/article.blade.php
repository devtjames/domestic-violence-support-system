<x-public-layout :title="$article->title">
    <main>
        <article>
            <header class="public-page-hero bg-gradient-to-br from-slate-950 via-slate-900 to-teal-950 py-20 text-white">
                <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    <p class="text-sm font-semibold uppercase text-teal-100">{{ $article->category->name }}</p>
                    <h1 class="mt-3 text-4xl font-bold leading-tight">{{ $article->title }}</h1>
                    <p class="mt-5 text-base leading-7 text-gray-100">{{ $article->short_description }}</p>
                </div>
            </header>

            <div class="bg-white py-12">
                <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    @if ($article->featured_image)
                        <img src="{{ Storage::url($article->featured_image) }}" alt="" class="mb-8 h-72 w-full rounded-md object-cover">
                    @endif

                    <div class="whitespace-pre-line text-base leading-8 text-gray-700">{{ $article->content }}</div>

                    <p class="mt-8 text-sm text-gray-500">
                        Published {{ $article->published_at?->format('M j, Y') }} by {{ $article->author->name }}.
                    </p>
                    <div class="mt-10 border-t border-gray-200 pt-6">
                        <a href="{{ route('public.awareness') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-800">Back to awareness</a>
                    </div>
                </div>
            </div>
        </article>
    </main>
</x-public-layout>
