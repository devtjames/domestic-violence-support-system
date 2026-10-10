<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-indigo-700">Notification center</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notifications</h2>
            </div>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    @method('PATCH')
                    <x-primary-button>Mark All Read</x-primary-button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('admin.partials.feedback')

            <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">
                Notifications use neutral wording and case references only. Case descriptions, private messages, internal notes, and contact instructions are never shown here.
            </div>

            <section class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
                <div class="divide-y divide-gray-200">
                    @forelse ($notifications as $notification)
                        @php
                            $data = $notification->data;
                        @endphp
                        <article class="p-5 {{ $notification->read_at ? 'bg-white' : 'bg-indigo-50/40' }}">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if ($notification->read_at === null)
                                            <span class="rounded-md bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200">Unread</span>
                                        @endif
                                        <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200">{{ str($data['category'] ?? 'case_update')->headline() }}</span>
                                    </div>
                                    <h3 class="mt-3 text-base font-semibold text-gray-900">{{ $data['title'] ?? 'Support case update.' }}</h3>
                                    <p class="mt-1 text-sm leading-6 text-gray-600">{{ $data['body'] ?? 'Open the related case for the latest workflow information.' }}</p>
                                    @if (! empty($data['case_reference']))
                                        <p class="mt-2 text-sm font-medium text-gray-700">Case {{ $data['case_reference'] }}</p>
                                    @endif
                                    <time datetime="{{ $notification->created_at->toIso8601String() }}" class="mt-2 block text-xs text-gray-500">
                                        {{ $notification->created_at->format('M j, Y g:i A') }}
                                    </time>
                                </div>
                                <a href="{{ route('notifications.open', $notification->id) }}" class="inline-flex w-fit justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                    Open
                                </a>
                            </div>
                        </article>
                    @empty
                        <p class="p-8 text-center text-sm text-gray-600">You do not have any notifications yet.</p>
                    @endforelse
                </div>
            </section>

            <div>{{ $notifications->links() }}</div>
        </div>
    </div>
</x-app-layout>
