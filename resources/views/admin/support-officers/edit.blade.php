<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Support Officer</h2>
            <a href="{{ route('admin.support-officers.show', $officer) }}" class="inline-flex w-fit rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">View Profile</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.feedback')
            @include('admin.support-officers.partials.form', [
                'action' => route('admin.support-officers.update', $officer),
                'method' => 'PATCH',
                'buttonText' => 'Save Changes',
            ])
        </div>
    </div>
</x-app-layout>
