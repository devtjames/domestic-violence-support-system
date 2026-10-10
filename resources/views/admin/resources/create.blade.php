<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Support Resource</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.feedback')
            @include('admin.resources.partials.form', [
                'action' => route('admin.resources.store'),
                'method' => 'POST',
                'buttonText' => 'Create Resource',
            ])
        </div>
    </div>
</x-app-layout>
