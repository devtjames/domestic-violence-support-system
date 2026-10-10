<x-public-layout :title="$title">
    <main>
        <section class="public-page-hero bg-gradient-to-br from-teal-950 via-teal-900 to-slate-950 py-20 text-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase text-teal-100">Awareness</p>
                    <h1 class="mt-3 text-4xl font-bold leading-tight">{{ $title }}</h1>
                    <p class="mt-5 text-base leading-7 text-teal-50">{{ $intro }}</p>
                </div>
            </div>
        </section>

        <section class="bg-white py-12">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
                @foreach ($sections as $section)
                    <article class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900">{{ $section['heading'] }}</h2>
                        <p class="mt-3 text-sm leading-6 text-gray-600">{{ $section['body'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    </main>
</x-public-layout>
