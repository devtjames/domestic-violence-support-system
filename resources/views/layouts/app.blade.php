<!DOCTYPE html>
@php($platformName = \App\Models\SystemSetting::platformName())
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ $platformName }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="app-body bg-slate-50 font-sans text-slate-900 antialiased" data-workspace="{{ Auth::user()->role }}" data-quick-exit-url="{{ route('public.quick-exit') }}">
        <div class="min-h-screen bg-slate-50">
            @include('layouts.navigation')

            <div class="min-w-0 pt-16 lg:pl-72 lg:pt-0">
                @isset($header)
                    <header class="workspace-header border-b border-slate-200/80 bg-white/90 backdrop-blur">
                        <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8 lg:py-6">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="app-workspace min-w-0 overflow-x-clip">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
