<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Abuse Type</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.feedback')
            @include('admin.abuse-types.partials.form', [
                'action' => route('admin.abuse-types.store'),
                'method' => 'POST',
                'buttonText' => 'Create Abuse Type',
            ])
        </div>
    </div>
</x-app-layout>
