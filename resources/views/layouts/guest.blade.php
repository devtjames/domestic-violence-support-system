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
    <body class="font-sans text-gray-900 antialiased" data-quick-exit-url="{{ route('public.quick-exit') }}">
        <div class="flex min-h-screen flex-col items-center bg-slate-50 px-4 pt-8 sm:justify-center sm:pt-0">
            <div class="w-full max-w-md text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center">
                    <x-application-logo class="h-16 w-16" />
                </a>
                <p class="mt-3 truncate text-sm font-semibold text-gray-900">{{ $platformName }}</p>
                <p class="mt-1 max-w-sm text-sm text-gray-600">Private access for registered users, support officers, and administrators.</p>
            </div>

            <div class="mt-6 w-full overflow-hidden rounded-2xl border border-slate-200 bg-white px-6 py-6 shadow-panel sm:max-w-md sm:px-8">
                {{ $slot }}
            </div>

            <a href="{{ route('home') }}" class="mt-6 text-sm font-medium text-teal-700 hover:text-teal-800">Back to public website</a>
            <div class="mt-4 flex flex-col items-center gap-3">
                <x-quick-exit-button />
                <p class="max-w-sm text-center text-xs leading-5 text-gray-600">Quick Exit does not erase browser history or device activity.</p>
            </div>
        </div>
    </body>
</html>
