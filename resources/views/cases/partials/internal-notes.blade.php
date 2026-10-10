<section class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
    <div>
        <h2 class="text-base font-semibold text-gray-900">Internal Case Notes</h2>
        <p class="mt-1 text-sm leading-6 text-gray-600">Private staff notes are never shown to registered users or anonymous visitors.</p>
    </div>

    <form method="POST" action="{{ route('cases.notes.store', $case) }}" class="mt-5 space-y-4">
        @csrf
        <div>
            <x-input-label for="note" value="Add Internal Note" />
            <textarea id="note" name="note" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('note') }}</textarea>
            <x-input-error :messages="$errors->get('note')" class="mt-2" />
        </div>
        <x-primary-button>Save Note</x-primary-button>
    </form>

    <div class="mt-6 space-y-4 border-t border-gray-200 pt-5">
        @forelse ($case->notes as $note)
            <article class="rounded-md border border-gray-200 bg-gray-50 px-4 py-3">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                    <p class="text-sm font-semibold text-gray-900">{{ $note->author?->name ?? 'Account unavailable' }}</p>
                    <time datetime="{{ $note->created_at->toISOString() }}" class="text-xs text-gray-500">
                        {{ $note->created_at->format('M j, Y g:i A') }}
                    </time>
                </div>
                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-700">{{ $note->note }}</p>
                @if ($note->updated_at->gt($note->created_at))
                    <p class="mt-3 text-xs text-gray-500">Updated {{ $note->updated_at->format('M j, Y g:i A') }}</p>
                @endif
            </article>
        @empty
            <div class="rounded-md border border-dashed border-gray-300 p-5 text-sm leading-6 text-gray-600">
                No internal notes have been added for this case yet.
            </div>
        @endforelse
    </div>
</section>
