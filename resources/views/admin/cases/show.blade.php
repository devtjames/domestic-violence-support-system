<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-700">Case management</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Support Case {{ $case->case_reference }}</h2>
            </div>
            <a href="{{ route('admin.cases.index') }}" class="inline-flex w-fit justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">All Cases</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="space-y-6 lg:col-span-2">
                @include('admin.partials.feedback')

                <article class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-status-badge :status="$case->status" />
                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold capitalize ring-1 {{ $case->urgency === 'critical' ? 'bg-rose-50 text-rose-700 ring-rose-200' : ($case->urgency === 'high' ? 'bg-amber-50 text-amber-700 ring-amber-200' : 'bg-gray-100 text-gray-700 ring-gray-200') }}">
                            {{ \Illuminate\Support\Str::headline($case->urgency) }}
                        </span>
                        <span class="text-sm text-gray-600">{{ $case->is_anonymous ? 'Anonymous' : 'Registered user' }}</span>
                    </div>

                    <h1 class="mt-6 text-2xl font-semibold text-gray-900">{{ $case->subject }}</h1>

                    <dl class="mt-6 grid gap-5 border-y border-gray-200 py-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Abuse Type</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $case->abuseType->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Assigned Officer</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $case->assignedOfficer?->name ?? 'Unassigned' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-900">Submitted By</dt>
                            <dd class="mt-1 text-sm text-gray-600">{{ $case->is_anonymous ? ($case->anonymous_nickname ?: 'Anonymous visitor') : $case->user?->name }}</dd>
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
                    </dl>

                    <div class="mt-6">
                        <h2 class="text-base font-semibold text-gray-900">Description</h2>
                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-gray-700">{{ $case->description }}</p>
                    </div>

                    @if ($case->safe_contact_instruction || $case->contact_email || $case->contact_phone)
                        <div class="mt-6 rounded-md border border-teal-200 bg-teal-50 p-4">
                            <h2 class="text-sm font-semibold text-teal-900">Contact and Safety Notes</h2>
                            <dl class="mt-3 grid gap-3 text-sm text-teal-900 sm:grid-cols-2">
                                <div>
                                    <dt class="font-semibold">Email</dt>
                                    <dd class="mt-1 break-all">{{ $case->contact_email ?: 'Not provided' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-semibold">Phone</dt>
                                    <dd class="mt-1">{{ $case->contact_phone ?: 'Not provided' }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="font-semibold">Safe Instructions</dt>
                                    <dd class="mt-1 whitespace-pre-line">{{ $case->safe_contact_instruction ?: 'Not provided' }}</dd>
                                </div>
                            </dl>
                        </div>
                    @endif
                </article>

                @include('cases.partials.attachments', [
                    'canUploadAttachments' => $case->status !== \App\Models\SupportCase::STATUS_CLOSED,
                    'uploadAction' => route('cases.attachments.store', $case),
                    'canDownloadAttachments' => true,
                ])

                @include('cases.partials.message-thread', [
                    'canSendMessage' => false,
                    'messageDisabledNotice' => $case->is_anonymous
                        ? 'Anonymous cases do not use registered-user private messaging.'
                        : 'Administrators can review this conversation but cannot send private case messages from this page.',
                ])

                @include('cases.partials.activity-list')
            </div>

            <aside class="space-y-6">
                @include('cases.partials.internal-notes')

                <section class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900">Update Case</h2>
                    <form method="POST" action="{{ route('admin.cases.update', $case) }}" class="mt-5 space-y-5">
                        @csrf
                        @method('PATCH')

                        <div>
                            <x-input-label for="assigned_officer_id" value="Assigned Officer" />
                            <select id="assigned_officer_id" name="assigned_officer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Unassigned</option>
                                @foreach ($officers as $officer)
                                    <option value="{{ $officer->id }}" @selected((string) old('assigned_officer_id', $case->assigned_officer_id) === (string) $officer->id)>{{ $officer->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('assigned_officer_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="urgency" value="Urgency" />
                            <select id="urgency" name="urgency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @foreach ($urgencies as $urgency)
                                    <option value="{{ $urgency }}" @selected(old('urgency', $case->urgency) === $urgency)>{{ \Illuminate\Support\Str::headline($urgency) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('urgency')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="status" value="Status" />
                            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" @selected(old('status', $case->status) === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="resolution_summary" value="Resolution Summary" />
                            <textarea id="resolution_summary" name="resolution_summary" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('resolution_summary', $case->resolution_summary) }}</textarea>
                            <x-input-error :messages="$errors->get('resolution_summary')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="action_note" value="Action Note" />
                            <textarea id="action_note" name="action_note" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('action_note') }}</textarea>
                            <x-input-error :messages="$errors->get('action_note')" class="mt-2" />
                        </div>

                        <x-primary-button>Save Case Update</x-primary-button>
                    </form>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
