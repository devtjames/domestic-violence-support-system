<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-teal-700">Private support</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Request Support</h2>
            </div>
            <x-quick-exit-button class="w-fit" />
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.feedback')

            <x-privacy-reminder class="mb-6" />

            @if ($abuseTypes->isEmpty())
                <div class="rounded-md border border-gray-200 bg-white p-6 text-sm text-gray-600 shadow-sm">
                    Support request categories are not available yet. Please try again later.
                </div>
            @else
                @include('cases.partials.form')
            @endif
        </div>
    </div>
</x-app-layout>
