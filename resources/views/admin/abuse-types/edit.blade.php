<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Abuse Type</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.feedback')
            @include('admin.abuse-types.partials.form', [
                'action' => route('admin.abuse-types.update', $abuseType),
                'method' => 'PATCH',
                'buttonText' => 'Save Changes',
            ])
        </div>
    </div>
</x-app-layout>
