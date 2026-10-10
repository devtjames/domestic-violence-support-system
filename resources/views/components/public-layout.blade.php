@props([
    'title' => null,
    'robots' => 'index, follow',
])

@php
    $platformName = \App\Models\SystemSetting::platformName();
    $pageTitle = $title ?: $platformName;
    $footerInformation = \App\Models\SystemSetting::getValue(
        \App\Models\SystemSetting::KEY_FOOTER_INFORMATION,
        'An academic awareness and support platform. It is not an emergency, legal, medical, or counselling service.'
    );
    $learnIsActive = request()->routeIs('public.about', 'public.types', 'public.warning-signs', 'public.awareness*');
    $resourcesAreActive = request()->routeIs('public.safety', 'public.resources', 'public.faqs', 'public.contact');
    $supportIsActive = request()->routeIs('public.get-support', 'anonymous.*');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="{{ $robots }}">

        <title>{{ $pageTitle }} | {{ $platformName }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="public-site bg-slate-50 font-sans text-slate-900 antialiased" data-quick-exit-url="{{ route('public.quick-exit') }}">
        <a href="#main-content" class="sr-only z-[60] rounded-lg bg-white px-4 py-3 font-semibold text-teal-800 shadow-lg focus:not-sr-only focus:fixed focus:left-4 focus:top-4">
            Skip to content
        </a>

        <div x-data="{ mobileOpen: false }" class="min-h-screen">
            <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 shadow-[0_1px_12px_rgba(15,23,42,0.04)] backdrop-blur-xl">
                <nav class="mx-auto flex h-20 max-w-[90rem] items-center gap-6 px-4 sm:px-6 lg:px-8" aria-label="Public navigation">
                    <a href="{{ route('home') }}" class="group flex shrink-0 items-center gap-3" aria-label="{{ $platformName }} home">
                        <x-application-logo class="h-10 w-10 shadow-sm transition duration-200 group-hover:scale-105" />
                        <span class="min-w-0">
                            <span class="block max-w-48 truncate text-[0.95rem] font-bold tracking-tight text-slate-950 sm:max-w-60">{{ $platformName }}</span>
                            <span class="hidden text-[0.68rem] font-semibold uppercase tracking-[0.14em] text-teal-700 sm:block">Awareness & support</span>
                        </span>
                    </a>

                    <div class="hidden flex-1 items-center justify-center gap-1 xl:flex">
                        <a href="{{ route('home') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold transition {{ request()->routeIs('home') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">Home</a>

                        <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @keydown.escape.stop="open = false" class="relative">
                            <button type="button" @click="open = ! open" :aria-expanded="open.toString()" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-semibold transition {{ $learnIsActive ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">
                                Learn
                                <svg class="h-4 w-4 transition duration-200" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                            </button>

                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="translate-y-1 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-1 opacity-0" @click.outside="open = false" class="absolute left-1/2 top-full w-[31rem] -translate-x-1/2 pt-3">
                                <div class="rounded-2xl border border-slate-200 bg-white p-2 shadow-[0_20px_60px_rgba(15,23,42,0.16)] ring-1 ring-slate-950/5">
                                    <div class="grid grid-cols-2 gap-1">
                                        <a href="{{ route('public.about') }}" class="group rounded-xl p-4 transition hover:bg-teal-50"><span class="block text-sm font-semibold text-slate-950 group-hover:text-teal-900">About the platform</span><span class="mt-1 block text-xs leading-5 text-slate-500">Our purpose, scope, and approach.</span></a>
                                        <a href="{{ route('public.types') }}" class="group rounded-xl p-4 transition hover:bg-teal-50"><span class="block text-sm font-semibold text-slate-950 group-hover:text-teal-900">Types of abuse</span><span class="mt-1 block text-xs leading-5 text-slate-500">Understand different forms of harm.</span></a>
                                        <a href="{{ route('public.warning-signs') }}" class="group rounded-xl p-4 transition hover:bg-teal-50"><span class="block text-sm font-semibold text-slate-950 group-hover:text-teal-900">Warning signs</span><span class="mt-1 block text-xs leading-5 text-slate-500">Recognize patterns of control and fear.</span></a>
                                        <a href="{{ route('public.awareness') }}" class="group rounded-xl p-4 transition hover:bg-teal-50"><span class="block text-sm font-semibold text-slate-950 group-hover:text-teal-900">Awareness library</span><span class="mt-1 block text-xs leading-5 text-slate-500">Practical, private reading at your pace.</span></a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @keydown.escape.stop="open = false" class="relative">
                            <button type="button" @click="open = ! open" :aria-expanded="open.toString()" class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-semibold transition {{ $resourcesAreActive ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">
                                Safety & Resources
                                <svg class="h-4 w-4 transition duration-200" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                            </button>

                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="translate-y-1 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-1 opacity-0" @click.outside="open = false" class="absolute left-1/2 top-full w-[31rem] -translate-x-1/2 pt-3">
                                <div class="rounded-2xl border border-slate-200 bg-white p-2 shadow-[0_20px_60px_rgba(15,23,42,0.16)] ring-1 ring-slate-950/5">
                                    <div class="grid grid-cols-2 gap-1">
                                        <a href="{{ route('public.safety') }}" class="group rounded-xl p-4 transition hover:bg-indigo-50"><span class="block text-sm font-semibold text-slate-950 group-hover:text-indigo-900">Safety planning</span><span class="mt-1 block text-xs leading-5 text-slate-500">Review practical safety considerations.</span></a>
                                        <a href="{{ route('public.resources') }}" class="group rounded-xl p-4 transition hover:bg-indigo-50"><span class="block text-sm font-semibold text-slate-950 group-hover:text-indigo-900">Support directory</span><span class="mt-1 block text-xs leading-5 text-slate-500">Find organizations and support options.</span></a>
                                        <a href="{{ route('public.faqs') }}" class="group rounded-xl p-4 transition hover:bg-indigo-50"><span class="block text-sm font-semibold text-slate-950 group-hover:text-indigo-900">FAQs</span><span class="mt-1 block text-xs leading-5 text-slate-500">Clear answers about privacy and access.</span></a>
                                        <a href="{{ route('public.contact') }}" class="group rounded-xl p-4 transition hover:bg-indigo-50"><span class="block text-sm font-semibold text-slate-950 group-hover:text-indigo-900">Contact</span><span class="mt-1 block text-xs leading-5 text-slate-500">Send a general platform enquiry.</span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ml-auto hidden shrink-0 items-center gap-2 xl:flex">
                        <x-quick-exit-button class="px-3.5" />
                        @auth
                            <a href="{{ route(Auth::user()->dashboardRouteName()) }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-950">Log in</a>
                        @endauth
                        <a href="{{ route('public.get-support') }}" class="rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800 {{ $supportIsActive ? 'ring-2 ring-teal-200 ring-offset-2' : '' }}">Get Support</a>
                    </div>

                    <div class="ml-auto flex items-center gap-2 xl:hidden">
                        <x-quick-exit-button label="Exit" class="min-h-10 px-3" />
                        <button type="button" @click="mobileOpen = ! mobileOpen" :aria-expanded="mobileOpen.toString()" aria-controls="public-mobile-navigation" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 text-slate-700 transition hover:bg-slate-50" aria-label="Toggle navigation">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path x-show="! mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6H20M4 12H20M4 18H20" />
                                <path x-show="mobileOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6L18 18" />
                            </svg>
                        </button>
                    </div>
                </nav>

                <div id="public-mobile-navigation" x-show="mobileOpen" x-cloak x-transition class="border-t border-slate-200 bg-white xl:hidden">
                    <div class="mx-auto max-h-[calc(100vh-5rem)] max-w-3xl overflow-y-auto px-4 py-5 sm:px-6">
                        <a href="{{ route('home') }}" class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('home') ? 'bg-teal-50 text-teal-800' : 'bg-slate-50 text-slate-800' }}">Home <span aria-hidden="true">→</span></a>

                        <div class="mt-5">
                            <p class="px-1 text-[0.68rem] font-bold uppercase tracking-[0.16em] text-slate-400">Learn</p>
                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                <a href="{{ route('public.about') }}" class="rounded-xl border border-slate-200 p-3 text-sm font-semibold text-slate-700 hover:border-teal-200 hover:bg-teal-50">About the platform</a>
                                <a href="{{ route('public.types') }}" class="rounded-xl border border-slate-200 p-3 text-sm font-semibold text-slate-700 hover:border-teal-200 hover:bg-teal-50">Types of abuse</a>
                                <a href="{{ route('public.warning-signs') }}" class="rounded-xl border border-slate-200 p-3 text-sm font-semibold text-slate-700 hover:border-teal-200 hover:bg-teal-50">Warning signs</a>
                                <a href="{{ route('public.awareness') }}" class="rounded-xl border border-slate-200 p-3 text-sm font-semibold text-slate-700 hover:border-teal-200 hover:bg-teal-50">Awareness library</a>
                            </div>
                        </div>

                        <div class="mt-5">
                            <p class="px-1 text-[0.68rem] font-bold uppercase tracking-[0.16em] text-slate-400">Safety & resources</p>
                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                <a href="{{ route('public.safety') }}" class="rounded-xl border border-slate-200 p-3 text-sm font-semibold text-slate-700 hover:border-indigo-200 hover:bg-indigo-50">Safety planning</a>
                                <a href="{{ route('public.resources') }}" class="rounded-xl border border-slate-200 p-3 text-sm font-semibold text-slate-700 hover:border-indigo-200 hover:bg-indigo-50">Support directory</a>
                                <a href="{{ route('public.faqs') }}" class="rounded-xl border border-slate-200 p-3 text-sm font-semibold text-slate-700 hover:border-indigo-200 hover:bg-indigo-50">FAQs</a>
                                <a href="{{ route('public.contact') }}" class="rounded-xl border border-slate-200 p-3 text-sm font-semibold text-slate-700 hover:border-indigo-200 hover:bg-indigo-50">Contact</a>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-2 border-t border-slate-200 pt-5 sm:grid-cols-2">
                            <a href="{{ route('public.get-support') }}" class="rounded-xl bg-teal-700 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-teal-800">Get Support</a>
                            @auth
                                <a href="{{ route(Auth::user()->dashboardRouteName()) }}" class="rounded-xl border border-slate-300 px-4 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Open Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="rounded-xl border border-slate-300 px-4 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Log in</a>
                                <a href="{{ route('register') }}" class="rounded-xl border border-slate-300 px-4 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50 sm:col-span-2">Create an account</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </header>

            <div id="main-content">{{ $slot }}</div>

            <footer class="border-t border-slate-800 bg-slate-950 text-white">
                <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                    <div class="grid gap-10 lg:grid-cols-12">
                        <div class="lg:col-span-5">
                            <div class="flex items-center gap-3">
                                <x-application-logo class="h-10 w-10" />
                                <div><p class="font-bold tracking-tight">{{ $platformName }}</p><p class="text-xs font-medium text-teal-300">Awareness, safety, and private support</p></div>
                            </div>
                            <p class="mt-5 max-w-md text-sm leading-6 text-slate-300">{{ $footerInformation }}</p>
                        </div>

                        <div class="sm:grid sm:grid-cols-3 sm:gap-8 lg:col-span-7">
                            <div>
                                <h2 class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Learn</h2>
                                <div class="mt-4 space-y-3 text-sm text-slate-300"><a href="{{ route('public.about') }}" class="block hover:text-white">About</a><a href="{{ route('public.types') }}" class="block hover:text-white">Types of abuse</a><a href="{{ route('public.warning-signs') }}" class="block hover:text-white">Warning signs</a><a href="{{ route('public.awareness') }}" class="block hover:text-white">Awareness library</a></div>
                            </div>
                            <div class="mt-8 sm:mt-0">
                                <h2 class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Find help</h2>
                                <div class="mt-4 space-y-3 text-sm text-slate-300"><a href="{{ route('public.safety') }}" class="block hover:text-white">Safety planning</a><a href="{{ route('public.resources') }}" class="block hover:text-white">Resources</a><a href="{{ route('public.get-support') }}" class="block hover:text-white">Get support</a><a href="{{ route('public.contact') }}" class="block hover:text-white">Contact</a></div>
                            </div>
                            <div class="mt-8 sm:mt-0">
                                <h2 class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Account</h2>
                                <div class="mt-4 space-y-3 text-sm text-slate-300">
                                    @auth
                                        <a href="{{ route(Auth::user()->dashboardRouteName()) }}" class="block hover:text-white">Dashboard</a>
                                    @else
                                        <a href="{{ route('login') }}" class="block hover:text-white">Log in</a><a href="{{ route('register') }}" class="block hover:text-white">Create account</a>
                                    @endauth
                                    <a href="{{ route('public.faqs') }}" class="block hover:text-white">FAQs</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 border-t border-slate-800 pt-6">
                        <div class="flex flex-col gap-4 text-xs leading-5 text-slate-400 lg:flex-row lg:items-center lg:justify-between"><p>Quick Exit leaves this website quickly but does not erase browser history, network logs, saved passwords, downloads, or device activity.</p><p class="shrink-0">&copy; {{ now()->year }} {{ $platformName }}</p></div>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
