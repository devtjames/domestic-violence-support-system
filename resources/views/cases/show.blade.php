<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-teal-700">Support case</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $case->case_reference }}</h2>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <x-quick-exit-button />
                <a href="{{ route('cases.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">My Cases</a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.feedback')

            <x-privacy-reminder class="mb-6" />

            <article class="rounded-md border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex flex-wrap items-center gap-3">
                    <x-status-badge :status="$case->status" />
                    <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold capitalize ring-1 {{ $case->urgency === 'critical' ? 'bg-rose-50 text-rose-700 ring-rose-200' : ($case->urgency === 'high' ? 'bg-amber-50 text-amber-700 ring-amber-200' : 'bg-gray-100 text-gray-700 ring-gray-200') }}">
                        {{ \Illuminate\Support\Str::headline($case->urgency) }}
                    </span>
                    <span class="text-sm text-gray-600">{{ $case->abuseType->name }}</span>
                </div>

                <h1 class="mt-6 text-2xl font-semibold text-gray-900">{{ $case->subject }}</h1>

                <dl class="mt-6 grid gap-5 border-y border-gray-200 py-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-semibold text-gray-900">Case Reference</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ $case->case_reference }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-gray-900">Submitted</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ $case->created_at->format('M j, Y g:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-gray-900">Preferred Contact</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ $contactMethods[$case->preferred_contact_method] ?? 'No preference' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-gray-900">Safe Contact Time</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ $case->safe_contact_time ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-gray-900">Assigned Officer</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ $case->assignedOfficer?->name ?? 'Not assigned yet' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-gray-900">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-600">{{ $case->updated_at->format('M j, Y g:i A') }}</dd>
                    </div>
                </dl>

                <div class="mt-6">
                    <h2 class="text-base font-semibold text-gray-900">Description</h2>
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-gray-700">{{ $case->description }}</p>
                </div>

                @if ($case->safe_contact_instruction)
                    <div class="mt-6 rounded-md border border-teal-200 bg-teal-50 p-4">
                        <h2 class="text-sm font-semibold text-teal-900">Safe Contact Instructions</h2>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-teal-900">{{ $case->safe_contact_instruction }}</p>
                    </div>
                @endif
            </article>

            @include('cases.partials.attachments', [
                'canUploadAttachments' => $case->status !== \App\Models\SupportCase::STATUS_CLOSED,
                'uploadAction' => route('cases.attachments.store', $case),
                'canDownloadAttachments' => true,
            ])

            @include('cases.partials.message-thread', [
                'canSendMessage' => $case->assigned_officer_id !== null && $case->status !== \App\Models\SupportCase::STATUS_CLOSED,
                'messageAction' => route('cases.messages.store', $case),
                'messageDisabledNotice' => $case->assigned_officer_id === null
                    ? 'A support officer must be assigned before private messages can be sent.'
                    : ($case->status === \App\Models\SupportCase::STATUS_CLOSED ? 'This case is closed, so new private messages are disabled.' : null),
            ])
        </div>
    </div>
</x-app-layout>
