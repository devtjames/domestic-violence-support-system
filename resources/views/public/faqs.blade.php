<x-public-layout title="FAQs">
    <main>
        <section class="public-page-hero bg-gradient-to-br from-teal-950 via-teal-900 to-slate-950 py-20 text-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase text-teal-100">Frequently asked questions</p>
                    <h1 class="mt-3 text-4xl font-bold leading-tight">Common questions</h1>
                    <p class="mt-5 text-base leading-7 text-teal-50">
                        Clear answers about public information, privacy, support requests, and role-based access.
                    </p>
                </div>
            </div>
        </section>

        <section class="bg-white py-12">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                @forelse ($categories as $category)
                    <section class="border-b border-gray-200 py-8 first:pt-0 last:border-b-0 last:pb-0">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">{{ $category->name }}</h2>
                            @if ($category->description)
                                <p class="mt-2 text-sm leading-6 text-gray-600">{{ $category->description }}</p>
                            @endif
                        </div>

                        <div class="mt-6 divide-y divide-gray-200">
                            @foreach ($category->faqs as $faq)
                                <article class="py-5 first:pt-0 last:pb-0">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $faq->question }}</h3>
                                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-600">{{ $faq->answer }}</p>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <div class="rounded-md border border-gray-200 bg-gray-50 p-6 text-sm leading-6 text-gray-600">
                        FAQs will appear here after they are published by an administrator.
                    </div>
                @endforelse
            </div>
        </section>
    </main>
</x-public-layout>
