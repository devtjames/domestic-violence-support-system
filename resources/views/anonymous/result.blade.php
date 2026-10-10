<x-public-layout title="Anonymous Case Status" robots="noindex, nofollow">
    <main>
        <section class="bg-indigo-950 py-16 text-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase text-indigo-100">Anonymous case status</p>
                    <h1 class="mt-3 text-4xl font-bold leading-tight">{{ $case->case_reference }}</h1>
                    <p class="mt-5 text-base leading-7 text-indigo-50">
                        This page shows limited tracking information. It does not display the original request description or private staff records.
                    </p>
                </div>
            </div>
        </section>

        <section class="bg-gray-50 py-12">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <article class="rounded-md border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-status-badge :status="$case->status" />
                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold capitalize ring-1 {{ $case->urgency === 'critical' ? 'bg-rose-50 text-rose-700 ring-rose-200' : ($case->urgency === 'high' ? 'bg-amber-50 text-amber-700 ring-amber-200' : 'bg-gray-100 text-gray-700 ring-gray-200') }}">
                            {{ \Illuminate\Support\Str::headline($case->urgency) }}
                        </span>
                    </div>

                    <dl class="mt-6 grid gap-5 border-y border-gray-200 py-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Case Reference</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $case->case_reference }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Status</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ \Illuminate\Support\Str::headline($case->status) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Submitted</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $case->created_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Latest Update</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $case->updated_at->format('M j, Y g:i A') }}</dd>
                        </div>
                    </dl>

                    <section class="mt-6">
                        <h2 class="text-base font-semibold text-gray-900">Public Responses</h2>
                        <p class="mt-3 text-sm leading-6 text-gray-600">
                            No public responses have been added yet. Case messaging and officer responses will be handled in later modules.
                        </p>
                    </section>

                    <x-privacy-reminder class="mt-6">
                        Keep your case reference and access code private. Quick Exit leaves this website quickly. Quick Exit does not erase browser history or device activity.
                    </x-privacy-reminder>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('anonymous.track.form') }}" class="inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Check Another Case</a>
                        <x-quick-exit-button />
                    </div>
                </article>
            </div>
        </section>
    </main>
</x-public-layout>
