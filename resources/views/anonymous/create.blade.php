<x-public-layout title="Anonymous Support" robots="noindex, nofollow">
    <main>
        <section class="bg-rose-950 py-16 text-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase text-rose-100">Anonymous support</p>
                    <h1 class="mt-3 text-4xl font-bold leading-tight">Submit a request without an account</h1>
                    <p class="mt-5 text-base leading-7 text-rose-50">
                        Share only what is needed for support review. This academic platform does not dispatch emergency assistance.
                    </p>
                </div>
            </div>
        </section>

        <section class="bg-gray-50 py-12">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 grid gap-4 md:grid-cols-2">
                    <div class="rounded-md border border-teal-200 bg-teal-50 p-4 text-sm leading-6 text-teal-900">
                        You will receive a case reference and access code once. Store both somewhere safe if you want to track the request.
                    </div>
                    <x-privacy-reminder class="p-4" />
                </div>

                @if ($abuseTypes->isEmpty())
                    <div class="rounded-md border border-gray-200 bg-white p-6 text-sm text-gray-600 shadow-sm">
                        Support request categories are not available yet. Please try again later.
                    </div>
                @else
                    @include('anonymous.partials.form')
                @endif
            </div>
        </section>
    </main>
</x-public-layout>
