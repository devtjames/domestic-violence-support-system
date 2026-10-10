<x-public-layout title="Track Anonymous Case" robots="noindex, nofollow">
    <main>
        <section class="bg-indigo-950 py-16 text-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase text-indigo-100">Anonymous tracking</p>
                    <h1 class="mt-3 text-4xl font-bold leading-tight">Check a case status</h1>
                    <p class="mt-5 text-base leading-7 text-indigo-50">
                        Enter the case reference and access code exactly as they were shown after submission.
                    </p>
                </div>
            </div>
        </section>

        <section class="bg-gray-50 py-12">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <form method="POST" action="{{ route('anonymous.track.lookup') }}" class="space-y-6 rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    @csrf

                    @if ($errors->any())
                        <div role="alert" class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                            <p class="font-semibold">The case reference or access code could not be verified.</p>
                        </div>
                    @endif

                    <div>
                        <x-input-label for="case_reference" value="Case Reference" />
                        <x-text-input id="case_reference" name="case_reference" :value="old('case_reference')" class="mt-1 block w-full font-mono" placeholder="DV-2026-000001" required />
                        <x-input-error :messages="$errors->get('case_reference')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="access_code" value="Access Code" />
                        <x-text-input id="access_code" name="access_code" :value="old('access_code')" class="mt-1 block w-full font-mono uppercase" required />
                        <x-input-error :messages="$errors->get('access_code')" class="mt-2" />
                    </div>

                    <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">
                        Lookup attempts are rate-limited. This page shows only limited status information for anonymous cases.
                    </div>

                    <x-privacy-reminder />

                    <div class="flex flex-col gap-3 border-t border-gray-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ route('anonymous.create') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Submit anonymous request</a>
                        <x-primary-button>Check Status</x-primary-button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</x-public-layout>
