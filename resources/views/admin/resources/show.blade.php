<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-700">Resource details</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $resource->organization_name }}</h2>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('admin.resources.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
                <a href="{{ route('admin.resources.edit', $resource) }}" class="inline-flex justify-center rounded-md bg-rose-700 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-800">Edit</a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.feedback')

            <article class="rounded-md border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex flex-wrap items-center gap-3">
                    <x-status-badge :status="$resource->status" />
                    <span class="rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200">{{ $resource->resource_type }}</span>
                    <span class="text-sm text-gray-600">{{ $resource->location }}</span>
                </div>

                <p class="mt-6 whitespace-pre-line text-base leading-8 text-gray-700">{{ $resource->description }}</p>

                <dl class="mt-8 grid gap-5 border-t border-gray-200 pt-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-semibold text-gray-900">Availability</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ $resource->availability }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-gray-900">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ $resource->phone ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-gray-900">Email</dt>
                        <dd class="mt-1 text-sm text-gray-600">
                            @if ($resource->email)
                                <a href="mailto:{{ $resource->email }}" class="font-medium text-rose-700 hover:text-rose-800">{{ $resource->email }}</a>
                            @else
                                Not provided
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-gray-900">Website</dt>
                        <dd class="mt-1 break-all text-sm text-gray-600">
                            @if ($resource->website)
                                <a href="{{ $resource->website }}" class="font-medium text-rose-700 hover:text-rose-800" rel="noopener noreferrer" target="_blank">{{ $resource->website }}</a>
                            @else
                                Not provided
                            @endif
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-semibold text-gray-900">Address</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ $resource->address ?: 'Not provided' }}</dd>
                    </div>
                </dl>
            </article>
        </div>
    </div>
</x-app-layout>
