<x-public-layout title="Awareness">
    <main>
        <section class="public-page-hero bg-gradient-to-br from-teal-950 via-teal-900 to-slate-950 py-20 text-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase text-teal-100">Awareness articles</p>
                    <h1 class="mt-3 text-4xl font-bold leading-tight">Learn at your own pace</h1>
                    <p class="mt-5 text-base leading-7 text-teal-50">
                        Short, practical articles for understanding abuse, privacy, safety planning, and how support workflows will operate.
                    </p>
                </div>
            </div>
        </section>

        <section class="bg-gray-50 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <form method="GET" action="{{ route('public.awareness') }}" class="grid gap-3 rounded-md border border-gray-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_220px_auto]">
                    <x-text-input name="search" :value="request('search')" placeholder="Search awareness articles" class="w-full" />
                    <select name="category" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <x-primary-button>Filter</x-primary-button>
                </form>

                <div class="mt-8 grid gap-5 lg:grid-cols-3">
                @forelse ($articles as $article)
                    <article class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                        @if ($article->featured_image)
                            <img src="{{ Storage::url($article->featured_image) }}" alt="" class="mb-5 h-40 w-full rounded-md object-cover">
                        @endif
                        <p class="text-xs font-semibold uppercase text-teal-700">{{ $article->category->name }}</p>
                        <h2 class="mt-3 text-xl font-semibold text-gray-900">
                            <a href="{{ route('public.awareness.show', $article->slug) }}" class="hover:text-teal-800">{{ $article->title }}</a>
                        </h2>
                        <p class="mt-3 text-sm leading-6 text-gray-600">{{ $article->short_description }}</p>
                        <a href="{{ route('public.awareness.show', $article->slug) }}" class="mt-5 inline-flex text-sm font-semibold text-teal-700 hover:text-teal-800">Read article</a>
                    </article>
                @empty
                    <div class="rounded-md border border-gray-200 bg-white p-6 text-sm text-gray-600 shadow-sm lg:col-span-3">
                        No published awareness articles match your search.
                    </div>
                @endforelse
                </div>

                <div class="mt-8">{{ $articles->links() }}</div>
            </div>
        </section>
    </main>
</x-public-layout>
