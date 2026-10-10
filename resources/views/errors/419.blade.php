<x-guest-layout>
    <div class="text-center">
        <p class="text-sm font-semibold text-rose-700">Session expired</p>
        <h1 class="mt-3 text-2xl font-semibold text-gray-900">Please refresh and try again.</h1>
        <p class="mt-3 text-sm leading-6 text-gray-600">
            Your secure session token expired before the form could be submitted.
        </p>
        <a href="{{ route('login') }}" class="mt-6 inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">
            Go to login
        </a>
    </div>
</x-guest-layout>
