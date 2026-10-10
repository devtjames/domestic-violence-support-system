<x-public-layout title="Anonymous Request Submitted" robots="noindex, nofollow">
    <main>
        <section class="bg-teal-950 py-16 text-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase text-teal-100">Request submitted</p>
                    <h1 class="mt-3 text-4xl font-bold leading-tight">Record these details now</h1>
                    <p class="mt-5 text-base leading-7 text-teal-50">
                        The access code is shown once. It is stored securely as a hash and cannot be recovered later.
                    </p>
                </div>
            </div>
        </section>

        <section class="bg-gray-50 py-12">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-md border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                    <dl class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Case Reference</dt>
                            <dd class="mt-2 rounded-md bg-gray-100 px-4 py-3 font-mono text-lg font-semibold text-gray-900">{{ $caseReference }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Access Code</dt>
                            <dd class="mt-2 rounded-md bg-gray-100 px-4 py-3 font-mono text-lg font-semibold text-gray-900">{{ $accessCode }}</dd>
                        </div>
                    </dl>

                    <x-privacy-reminder class="mt-6">
                        Anyone with both values can check the limited anonymous case status page. Keep them private and use a safer device where possible. Quick Exit does not erase browser history or device activity.
                    </x-privacy-reminder>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('anonymous.track.form') }}" class="inline-flex justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Track Anonymous Case</a>
                        <x-quick-exit-button />
                    </div>
                </div>
            </div>
        </section>
    </main>
</x-public-layout>
