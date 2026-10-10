@php
    $viewer = auth()->user();
    $canSendMessage = $canSendMessage ?? false;
    $messageAction = $messageAction ?? null;
    $messageDisabledNotice = $messageDisabledNotice ?? null;
@endphp

<section class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-base font-semibold text-gray-900">Conversation Thread</h2>
            <p class="mt-1 text-sm leading-6 text-gray-600">Messages are private to this case and visible only to authorized participants.</p>
        </div>
        @if (($case->unread_messages_count ?? 0) > 0)
            <span class="inline-flex w-fit rounded-md bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-700 ring-1 ring-teal-200">
                {{ $case->unread_messages_count }} unread
            </span>
        @endif
    </div>

    <div class="mt-6 space-y-4">
        @forelse ($case->messages as $message)
            @php
                $isMine = $viewer && $message->sender_id === $viewer->id;
                $senderLabel = $message->sender?->name ?? 'Account unavailable';
            @endphp

            <article class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-2xl rounded-md border px-4 py-3 {{ $isMine ? 'border-teal-200 bg-teal-50' : 'border-gray-200 bg-gray-50' }}">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm font-semibold {{ $isMine ? 'text-teal-950' : 'text-gray-900' }}">{{ $isMine ? 'You' : $senderLabel }}</p>
                        <time datetime="{{ $message->created_at->toISOString() }}" class="text-xs text-gray-500">
                            {{ $message->created_at->format('M j, Y g:i A') }}
                        </time>
                    </div>
                    <p class="mt-3 whitespace-pre-line text-sm leading-6 {{ $isMine ? 'text-teal-950' : 'text-gray-700' }}">{{ $message->message }}</p>
                    <p class="mt-3 text-xs text-gray-500">
                        @if ($message->read_at)
                            Read {{ $message->read_at->format('M j, Y g:i A') }}
                        @elseif ($isMine)
                            Sent, not read yet
                        @else
                            Unread
                        @endif
                    </p>
                </div>
            </article>
        @empty
            <div class="rounded-md border border-dashed border-gray-300 p-5 text-sm leading-6 text-gray-600">
                No private messages have been sent for this case yet.
            </div>
        @endforelse
    </div>

    @if ($canSendMessage && $messageAction)
        <form method="POST" action="{{ $messageAction }}" class="mt-6 space-y-4 border-t border-gray-200 pt-5">
            @csrf
            <div>
                <x-input-label for="message" value="New Private Message" />
                <textarea id="message" name="message" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('message') }}</textarea>
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
            </div>
            <x-primary-button>Send Message</x-primary-button>
        </form>
    @elseif ($messageDisabledNotice)
        <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">
            {{ $messageDisabledNotice }}
        </div>
    @endif
</section>
