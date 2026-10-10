<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-teal-700">Registered user area</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Dashboard') }}
                </h2>
            </div>
            <span class="inline-flex w-fit rounded-md bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-700 ring-1 ring-emerald-200">
                {{ ucfirst(Auth::user()->account_status) }}
            </span>
        </div>
    </x-slot>

    @php
        $cardClasses = [
            'teal' => 'bg-teal-50 text-teal-800 ring-teal-100',
            'emerald' => 'bg-emerald-50 text-emerald-800 ring-emerald-100',
            'indigo' => 'bg-indigo-50 text-indigo-800 ring-indigo-100',
            'slate' => 'bg-slate-50 text-slate-800 ring-slate-100',
        ];
    @endphp

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <x-privacy-reminder />

            <section class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg lg:col-span-2">
                    <p class="text-sm font-semibold uppercase text-teal-700">Welcome back</p>
                    <h3 class="mt-2 text-2xl font-semibold text-gray-900">{{ Auth::user()->name }}</h3>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600">
                        Track your support requests using case references and status updates. Dashboard cards avoid showing private descriptions, message text, and safe contact details.
                    </p>
                    <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('cases.create') }}" class="inline-flex justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">Request Support</a>
                        <a href="{{ route('cases.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">My Cases</a>
                        <a href="{{ route('cases.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Read Messages</a>
                        <a href="{{ route('public.resources') }}" class="inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">View Resources</a>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-base font-semibold text-gray-900">Account Access</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-600">Role</dt>
                            <dd class="font-medium text-gray-900">User</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-600">Profile</dt>
                            <dd>
                                <a href="{{ route('profile.edit') }}" class="font-medium text-teal-700 hover:text-teal-800">Manage</a>
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section class="metric-grid grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($summaryCards as $card)
                    <article class="metric-card rounded-lg p-5 ring-1 {{ $cardClasses[$card['tone']] ?? $cardClasses['slate'] }}">
                        <p class="text-sm font-medium">{{ $card['label'] }}</p>
                        <p class="mt-3 text-3xl font-semibold tracking-normal">{{ number_format($card['value']) }}</p>
                        <p class="mt-2 text-sm leading-6">{{ $card['helper'] }}</p>
                    </article>
                @endforeach
            </section>

            <section class="grid gap-6 xl:grid-cols-5">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg xl:col-span-3">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">My Recent Cases</h3>
                            <p class="mt-1 text-sm text-gray-600">References, status, urgency, and unread counts only.</p>
                        </div>
                        <a href="{{ route('cases.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-800">View all</a>
                    </div>

                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-normal text-gray-500">
                                    <th scope="col" class="py-3 pr-4">Reference</th>
                                    <th scope="col" class="px-4 py-3">Type</th>
                                    <th scope="col" class="px-4 py-3">Urgency</th>
                                    <th scope="col" class="px-4 py-3">Status</th>
                                    <th scope="col" class="py-3 pl-4">Updated</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($recentCases as $case)
                                    <tr>
                                        <td class="whitespace-nowrap py-3 pr-4">
                                            <a href="{{ route('cases.show', $case) }}" class="font-semibold text-teal-700 hover:text-teal-800">{{ $case->case_reference }}</a>
                                            @if ($case->unread_messages_count > 0)
                                                <span class="ml-2 inline-flex rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200">{{ $case->unread_messages_count }}</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $case->abuseType?->name ?? 'Unspecified' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ str($case->urgency)->headline() }}</td>
                                        <td class="whitespace-nowrap px-4 py-3"><x-status-badge :status="$case->status" /></td>
                                        <td class="whitespace-nowrap py-3 pl-4 text-gray-600">{{ $case->updated_at->format('M j, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-sm text-gray-500">Your submitted support requests will appear here.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg xl:col-span-2">
                    <h3 class="text-base font-semibold text-gray-900">Recent Messages</h3>
                    <p class="mt-1 text-sm text-gray-600">Message content stays inside the case conversation.</p>

                    <div class="mt-5 space-y-4">
                        @forelse ($recentMessages as $message)
                            <article class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <a href="{{ route('cases.show', $message->supportCase) }}" class="text-sm font-semibold text-teal-700 hover:text-teal-800">{{ $message->supportCase->case_reference }}</a>
                                        <p class="mt-1 text-sm text-gray-600">
                                            {{ $message->sender_id === Auth::id() ? 'You sent a message' : 'Support officer replied' }}
                                        </p>
                                    </div>
                                    @if ($message->receiver_id === Auth::id() && $message->read_at === null)
                                        <span class="shrink-0 rounded-md bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200">Unread</span>
                                    @endif
                                </div>
                                <time datetime="{{ $message->created_at->toIso8601String() }}" class="mt-2 block text-xs text-gray-500">{{ $message->created_at->format('M j, Y g:i A') }}</time>
                            </article>
                        @empty
                            <p class="rounded-md bg-gray-50 p-4 text-sm text-gray-600">Private case messages will appear here after a support officer is assigned.</p>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-base font-semibold text-gray-900">Latest Case Updates</h3>
                    <p class="mt-1 text-sm text-gray-600">Public-safe activity labels for your cases.</p>

                    <div class="mt-5 space-y-4">
                        @forelse ($recentCaseUpdates as $activity)
                            <article class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
                                <a href="{{ route('cases.show', $activity->supportCase) }}" class="text-sm font-semibold text-teal-700 hover:text-teal-800">{{ $activity->supportCase->case_reference }}</a>
                                <p class="mt-1 text-sm text-gray-700">{{ $activity->label() }}</p>
                                <time datetime="{{ $activity->created_at->toIso8601String() }}" class="mt-1 block text-xs text-gray-500">{{ $activity->created_at->format('M j, Y g:i A') }}</time>
                            </article>
                        @empty
                            <p class="rounded-md bg-gray-50 p-4 text-sm text-gray-600">Case updates will appear here after a request is submitted.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Awareness Articles</h3>
                            <p class="mt-1 text-sm text-gray-600">Recently published guidance.</p>
                        </div>
                        <a href="{{ route('public.awareness') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-800">Browse</a>
                    </div>

                    <div class="mt-5 space-y-4">
                        @forelse ($awarenessArticles as $article)
                            <article class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
                                <a href="{{ route('public.awareness.show', $article) }}" class="text-sm font-semibold text-gray-900 hover:text-teal-700">{{ $article->title }}</a>
                                <p class="mt-1 text-xs font-medium text-teal-700">{{ $article->category?->name ?? 'Awareness' }}</p>
                            </article>
                        @empty
                            <p class="rounded-md bg-gray-50 p-4 text-sm text-gray-600">Published awareness articles will appear here.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Support Resources</h3>
                            <p class="mt-1 text-sm text-gray-600">Active organizations listed by the platform.</p>
                        </div>
                        <a href="{{ route('public.resources') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-800">View</a>
                    </div>

                    <div class="mt-5 space-y-4">
                        @forelse ($supportResources as $resource)
                            <article class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0">
                                <p class="text-sm font-semibold text-gray-900">{{ $resource->organization_name }}</p>
                                <p class="mt-1 text-sm text-gray-600">{{ $resource->resource_type }} &middot; {{ $resource->location }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $resource->availability }}</p>
                            </article>
                        @empty
                            <p class="rounded-md bg-gray-50 p-4 text-sm text-gray-600">Active support resources will appear here.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
