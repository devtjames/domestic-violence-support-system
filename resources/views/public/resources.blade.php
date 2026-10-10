<x-public-layout title="Resources">
    <main>
        <section class="public-page-hero bg-gradient-to-br from-indigo-950 via-indigo-900 to-slate-950 py-20 text-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase text-indigo-100">Support resources</p>
                    <h1 class="mt-3 text-4xl font-bold leading-tight">Organizations and support options</h1>
                    <p class="mt-5 text-base leading-7 text-indigo-50">
                        Browse active resources maintained by administrators. This academic platform does not dispatch emergency help or replace verified professional services.
                    </p>
                </div>
            </div>
        </section>

        <section class="bg-gray-50 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <form method="GET" action="{{ route('public.resources') }}" class="grid gap-3 rounded-md border border-gray-200 bg-white p-4 shadow-sm lg:grid-cols-[1fr_220px_180px_auto]">
                    <x-text-input name="search" :value="request('search')" placeholder="Search resources" class="w-full" />
                    <select name="resource_type" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All resource types</option>
                        @foreach ($resourceTypes as $type)
                            <option value="{{ $type }}" @selected(request('resource_type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                    <x-text-input name="location" :value="request('location')" placeholder="Location" class="w-full" />
                    <x-primary-button>Filter</x-primary-button>
                </form>

                <div class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @forelse ($resources as $resource)
                        <article class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200">{{ $resource->resource_type }}</span>
                                <span class="text-xs font-medium text-gray-500">{{ $resource->location }}</span>
                            </div>
                            <h2 class="mt-4 text-lg font-semibold text-gray-900">{{ $resource->organization_name }}</h2>
                            <p class="mt-3 text-sm leading-6 text-gray-600">{{ $resource->description }}</p>
                            <dl class="mt-5 space-y-3 text-sm">
                                <div>
                                    <dt class="font-semibold text-gray-900">Availability</dt>
                                    <dd class="mt-1 text-gray-600">{{ $resource->availability }}</dd>
                                </div>
                                @if ($resource->phone)
                                    <div>
                                        <dt class="font-semibold text-gray-900">Phone</dt>
                                        <dd class="mt-1 text-gray-600">{{ $resource->phone }}</dd>
                                    </div>
                                @endif
                                @if ($resource->email)
                                    <div>
                                        <dt class="font-semibold text-gray-900">Email</dt>
                                        <dd class="mt-1 break-all text-gray-600">
                                            <a href="mailto:{{ $resource->email }}" class="font-medium text-indigo-700 hover:text-indigo-800">{{ $resource->email }}</a>
                                        </dd>
                                    </div>
                                @endif
                                @if ($resource->website)
                                    <div>
                                        <dt class="font-semibold text-gray-900">Website</dt>
                                        <dd class="mt-1 break-all text-gray-600">
                                            <a href="{{ $resource->website }}" class="font-medium text-indigo-700 hover:text-indigo-800" rel="noopener noreferrer" target="_blank">Visit website</a>
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </article>
                    @empty
                        <div class="rounded-md border border-gray-200 bg-white p-6 text-sm text-gray-600 shadow-sm md:col-span-2 lg:col-span-3">
                            No active resources match the selected filters.
                        </div>
                    @endforelse
                </div>

                <div class="mt-6">{{ $resources->links() }}</div>
            </div>
        </section>
    </main>
</x-public-layout>
