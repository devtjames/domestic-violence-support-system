<x-public-layout title="Home">
    @php
        $platformName = $settings['platform_name'] ?? \App\Models\SystemSetting::platformName();
        $homepageEyebrow = $settings['homepage_eyebrow'] ?? 'Private awareness and support';
        $homepageHeading = $settings['homepage_heading'] ?? $platformName;
        $homepageIntro = $settings['homepage_intro'] ?? 'Learn about domestic violence, understand warning signs, and find a safer path to request support.';
    @endphp

    <main>
        <section class="public-home-hero relative min-h-[calc(100vh-5rem)] overflow-hidden bg-slate-950">
            <img src="{{ asset('images/support-hero.png') }}" alt="A calm support room with comfortable chairs and daylight" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/85 to-slate-950/35"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/65 via-transparent to-transparent"></div>
            <div class="relative mx-auto grid min-h-[calc(100vh-5rem)] max-w-7xl items-center gap-12 px-4 py-20 sm:px-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:px-8">
                <div class="max-w-3xl text-white">
                    <p class="inline-flex rounded-full border border-white/20 bg-white/10 px-3.5 py-2 text-xs font-bold uppercase tracking-[0.15em] text-teal-100 backdrop-blur">{{ $homepageEyebrow }}</p>
                    <h1 class="mt-6 text-4xl font-bold leading-[1.06] tracking-[-0.045em] sm:text-5xl lg:text-7xl">{{ $homepageHeading }}</h1>
                    <p class="mt-6 max-w-2xl text-base leading-7 text-slate-100 sm:text-lg">
                        {{ $homepageIntro }}
                    </p>
                    <div class="mt-9 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <a href="{{ route('public.get-support') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-teal-500 px-6 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-teal-950/20 transition hover:bg-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-200">
                            Get Support
                        </a>
                        <a href="{{ route('anonymous.create') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-white/30 bg-white/10 px-6 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white">
                            Anonymous Help
                        </a>
                        <x-quick-exit-button class="min-h-12 px-6 py-3 focus:ring-rose-200" />
                    </div>
                    <p class="mt-5 max-w-xl text-xs leading-5 text-slate-300">
                        Quick Exit moves away from this site. Quick Exit does not erase browser history.
                    </p>
                </div>

                <aside class="hidden rounded-3xl border border-white/15 bg-slate-950/55 p-6 text-white shadow-2xl backdrop-blur-md lg:block">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">You have options</p>
                    <h2 class="mt-3 text-xl font-semibold tracking-tight">Move at a pace that feels safer.</h2>
                    <div class="mt-6 space-y-4">
                        <div class="flex gap-3"><span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-teal-400/15 text-xs font-bold text-teal-200">1</span><p class="text-sm leading-6 text-slate-200">Read public information without creating an account.</p></div>
                        <div class="flex gap-3"><span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-teal-400/15 text-xs font-bold text-teal-200">2</span><p class="text-sm leading-6 text-slate-200">Request help anonymously and keep your access code private.</p></div>
                        <div class="flex gap-3"><span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-teal-400/15 text-xs font-bold text-teal-200">3</span><p class="text-sm leading-6 text-slate-200">Use a protected account for ongoing case communication.</p></div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="bg-white py-16 lg:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid items-start gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,0.8fr)]">
                    <div class="max-w-3xl">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-700">Awareness first</p>
                        <h2 class="mt-4 text-3xl font-semibold leading-tight text-slate-950 sm:text-4xl">Domestic violence can include control, fear, harm, and isolation.</h2>
                        <p class="mt-5 text-base leading-8 text-slate-600">This platform presents educational information in plain language and keeps public learning, registered support, anonymous requests, and staff-only case management clearly separated.</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                        <a href="{{ route('public.awareness') }}" class="group flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 transition hover:border-teal-200 hover:bg-teal-50"><span>Learn privately</span><span class="text-teal-700 transition group-hover:translate-x-1">→</span></a>
                        <a href="{{ route('public.safety') }}" class="group flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 transition hover:border-teal-200 hover:bg-teal-50"><span>Plan for safety</span><span class="text-teal-700 transition group-hover:translate-x-1">→</span></a>
                        <a href="{{ route('public.resources') }}" class="group flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-800 transition hover:border-teal-200 hover:bg-teal-50"><span>Find resources</span><span class="text-teal-700 transition group-hover:translate-x-1">→</span></a>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-gray-50 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase text-teal-700">Types of abuse</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900">Recognize different forms</h2>
                    </div>
                    <a href="{{ route('public.types') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-800">View all types</a>
                </div>
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($abuseTypes as $type)
                        <article class="rounded-md border border-gray-200 bg-white p-5 shadow-sm">
                            <h3 class="text-base font-semibold text-gray-900">{{ $type['title'] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-gray-600">{{ $type['description'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="bg-white py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-10 lg:grid-cols-2">
                    <div>
                        <p class="text-sm font-semibold uppercase text-rose-700">Warning signs</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900">Patterns may be subtle at first.</h2>
                        <div class="mt-6 space-y-4">
                            @foreach ($warningSigns as $sign)
                                <div class="border-l-4 border-rose-200 pl-4">
                                    <h3 class="text-base font-semibold text-gray-900">{{ $sign['title'] }}</h3>
                                    <p class="mt-1 text-sm leading-6 text-gray-600">{{ $sign['description'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="rounded-md bg-teal-900 p-8 text-white">
                        <h2 class="text-2xl font-semibold">How the platform helps</h2>
                        <div class="mt-6 space-y-5 text-sm leading-6 text-teal-50">
                            <p>Visitors can read awareness information without creating an account.</p>
                            <p>Registered users will have private account-based support workflows in later modules.</p>
                            <p>Anonymous support tracking uses a case reference and secure access code.</p>
                            <p>Support officers and administrators use protected dashboards with backend authorization.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-gray-50 py-12">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                <div>
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase text-teal-700">Latest awareness</p>
                            <h2 class="mt-2 text-2xl font-semibold text-gray-900">Read and prepare</h2>
                        </div>
                        <a href="{{ route('public.awareness') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-800">More</a>
                    </div>
                    <div class="mt-6 space-y-4">
                        @forelse ($articles as $article)
                            <article class="rounded-md border border-gray-200 bg-white p-5 shadow-sm">
                                <p class="text-xs font-semibold uppercase text-teal-700">{{ $article->category->name }}</p>
                                <h3 class="mt-2 text-base font-semibold text-gray-900">
                                    <a href="{{ route('public.awareness.show', $article->slug) }}" class="hover:text-teal-800">{{ $article->title }}</a>
                                </h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600">{{ $article->short_description }}</p>
                            </article>
                        @empty
                            <div class="rounded-md border border-gray-200 bg-white p-5 text-sm text-gray-600 shadow-sm">
                                Published awareness articles will appear here after an administrator publishes them.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div>
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase text-indigo-700">Support resources</p>
                            <h2 class="mt-2 text-2xl font-semibold text-gray-900">Support resources</h2>
                        </div>
                        <a href="{{ route('public.resources') }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-800">More</a>
                    </div>
                    <div class="mt-6 space-y-4">
                        @forelse ($resources as $resource)
                            <article class="rounded-md border border-gray-200 bg-white p-5 shadow-sm">
                                <p class="text-xs font-semibold uppercase text-indigo-700">{{ $resource->resource_type }}</p>
                                <h3 class="mt-2 text-base font-semibold text-gray-900">{{ $resource->organization_name }}</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600">{{ \Illuminate\Support\Str::limit($resource->description, 130) }}</p>
                            </article>
                        @empty
                            <div class="rounded-md border border-gray-200 bg-white p-5 text-sm text-gray-600 shadow-sm">
                                Active support resources will appear here after an administrator adds them.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-md border border-teal-200 bg-teal-50 p-6 sm:p-8">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="max-w-3xl">
                            <h2 class="text-2xl font-semibold text-gray-900">Need a private account?</h2>
                            <p class="mt-2 text-sm leading-6 text-gray-700">
                                Create an account for future case tracking and private support messages, or review support options without signing in.
                            </p>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('register') }}" class="rounded-md bg-teal-700 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-teal-800">Register</a>
                            <a href="{{ route('login') }}" class="rounded-md border border-teal-700 px-4 py-2 text-center text-sm font-semibold text-teal-800 hover:bg-white">Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</x-public-layout>
