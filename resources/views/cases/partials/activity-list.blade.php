<section class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
    <div>
        <h2 class="text-base font-semibold text-gray-900">Case Activity History</h2>
        <p class="mt-1 text-sm leading-6 text-gray-600">Staff-only audit trail for major case actions. Private note and message contents are not copied into this history.</p>
    </div>
    <div class="mt-5 space-y-4">
        @forelse ($case->activities as $activity)
            <article class="rounded-md border border-gray-200 p-4">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $activity->label() }}</p>
                        <p class="mt-1 text-sm leading-6 text-gray-600">{{ $activity->description }}</p>
                    </div>
                    <time datetime="{{ $activity->created_at->toISOString() }}" class="text-xs text-gray-500">{{ $activity->created_at->format('M j, Y g:i A') }}</time>
                </div>
                <dl class="mt-3 grid gap-3 text-xs text-gray-600 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <dt class="font-semibold text-gray-800">Actor</dt>
                        <dd class="mt-1">{{ $activity->actor?->name ?? 'System' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-800">Previous</dt>
                        <dd class="mt-1">{{ $activity->previous_value ?: 'None' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-800">New</dt>
                        <dd class="mt-1">{{ $activity->new_value ?: 'None' }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-gray-800">IP Address</dt>
                        <dd class="mt-1 break-all">{{ $activity->ip_address ?: 'Not recorded' }}</dd>
                    </div>
                </dl>
            </article>
        @empty
            <p class="text-sm leading-6 text-gray-600">No activity has been recorded yet.</p>
        @endforelse
    </div>
</section>
