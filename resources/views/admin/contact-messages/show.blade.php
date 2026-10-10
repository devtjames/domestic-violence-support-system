<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-700">General enquiry</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Contact Message</h2>
            </div>
            <a href="{{ route('admin.contact-messages.index') }}" class="inline-flex w-fit justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">All Messages</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="space-y-6 lg:col-span-2">
                @include('admin.partials.feedback')

                <article class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-status-badge :status="$message->status" />
                        <span class="text-sm text-gray-600">General enquiry</span>
                    </div>

                    <h1 class="mt-6 text-2xl font-semibold text-gray-900">{{ $message->subject }}</h1>

                    <dl class="mt-6 grid gap-5 border-y border-gray-200 py-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Name</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $message->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Email</dt>
                            <dd class="mt-1 break-all text-sm text-gray-600">{{ $message->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Received</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $message->created_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Reviewed</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $message->reviewed_at?->format('M j, Y g:i A') ?? 'Not reviewed' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6">
                        <h2 class="text-base font-semibold text-gray-900">Message</h2>
                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-gray-700">{{ $message->message }}</p>
                    </div>

                    <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">
                        This is a general enquiry and must remain separate from support case workflows.
                    </div>
                </article>
            </div>

            <aside class="space-y-6">
                <section class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900">Status Handling</h2>
                    <form method="POST" action="{{ route('admin.contact-messages.update', $message) }}" class="mt-5 space-y-5">
                        @csrf
                        @method('PATCH')

                        <div>
                            <x-input-label for="status" value="Status" />
                            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" @selected(old('status', $message->status) === $status)>{{ str($status)->headline() }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="admin_note" value="Admin Note" />
                            <textarea id="admin_note" name="admin_note" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('admin_note', $message->admin_note) }}</textarea>
                            <x-input-error :messages="$errors->get('admin_note')" class="mt-2" />
                        </div>

                        <x-primary-button>Save Status</x-primary-button>
                    </form>
                </section>

                <section class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900">Message Lifecycle</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-600">Resolved</dt>
                            <dd class="text-right font-medium text-gray-900">{{ $message->resolved_at?->format('M j, Y') ?? 'Not resolved' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-600">Updated</dt>
                            <dd class="text-right font-medium text-gray-900">{{ $message->updated_at->format('M j, Y') }}</dd>
                        </div>
                    </dl>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
