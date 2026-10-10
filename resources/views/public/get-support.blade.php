<x-public-layout title="Get Support" robots="noindex, nofollow">
    <main>
        <section class="public-page-hero bg-gradient-to-br from-rose-950 via-rose-900 to-slate-950 py-20 text-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase text-rose-100">Get support</p>
                    <h1 class="mt-3 text-4xl font-bold leading-tight">Choose a safer way to continue</h1>
                    <p class="mt-5 text-base leading-7 text-rose-50">
                        This academic platform does not dispatch emergency assistance. If there is immediate danger, use a verified local emergency service or trusted local support contact.
                    </p>
                </div>
            </div>
        </section>

        <section class="bg-gray-50 py-12">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
                <div class="lg:col-span-3">
                    <x-privacy-reminder />
                </div>

                <article class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Registered support</h2>
                    <p class="mt-3 text-sm leading-6 text-gray-600">Submit a private support request from a protected account and return later to view its status.</p>
                    @auth
                        @if (Auth::user()->isRegisteredUser())
                            <a href="{{ route('cases.create') }}" class="mt-5 inline-flex rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Request Support</a>
                        @else
                            <a href="{{ route(Auth::user()->dashboardRouteName()) }}" class="mt-5 inline-flex rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Dashboard</a>
                        @endif
                    @else
                        <a href="{{ route('register') }}" class="mt-5 inline-flex rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Register</a>
                    @endauth
                </article>

                <article id="anonymous" class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Anonymous help</h2>
                    <p class="mt-3 text-sm leading-6 text-gray-600">Submit without an account, then track limited status information with a case reference and access code.</p>
                    <div class="mt-5 flex flex-col gap-2 sm:flex-row">
                        <a href="{{ route('anonymous.create') }}" class="inline-flex justify-center rounded-md bg-rose-700 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-800">Anonymous Request</a>
                        <a href="{{ route('anonymous.track.form') }}" class="inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50">Track Case</a>
                    </div>
                </article>

                <article class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Already registered</h2>
                    <p class="mt-3 text-sm leading-6 text-gray-600">Sign in to reach your protected dashboard, submitted cases, and profile area.</p>
                    @auth
                        @if (Auth::user()->isRegisteredUser())
                            <a href="{{ route('cases.index') }}" class="mt-5 inline-flex rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50">My Cases</a>
                        @else
                            <a href="{{ route(Auth::user()->dashboardRouteName()) }}" class="mt-5 inline-flex rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50">Dashboard</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="mt-5 inline-flex rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50">Login</a>
                    @endauth
                </article>
            </div>
        </section>
    </main>
</x-public-layout>
