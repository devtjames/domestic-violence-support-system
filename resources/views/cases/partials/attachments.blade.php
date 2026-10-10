@php
    $canUploadAttachments = $canUploadAttachments ?? false;
    $uploadAction = $uploadAction ?? null;
    $canDownloadAttachments = $canDownloadAttachments ?? true;
@endphp

<section class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-base font-semibold text-gray-900">Attachments</h2>
            <p class="mt-1 text-sm leading-6 text-gray-600">Approved files only: JPG, JPEG, PNG, or PDF. Each file must be 5 MB or smaller.</p>
        </div>
        <span class="inline-flex w-fit rounded-md bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200">
            {{ $case->attachments->count() }} {{ \Illuminate\Support\Str::plural('file', $case->attachments->count()) }}
        </span>
    </div>

    <div class="mt-5 space-y-3">
        @forelse ($case->attachments as $attachment)
            <div class="flex flex-col gap-3 rounded-md border border-gray-200 bg-gray-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-gray-900">{{ $attachment->original_name }}</p>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ strtoupper($attachment->extension) }} · {{ $attachment->formattedSize() }} · Uploaded {{ $attachment->created_at->format('M j, Y g:i A') }}
                        @if ($attachment->uploader)
                            by {{ $attachment->uploader->name }}
                        @elseif ($attachment->upload_source === \App\Models\CaseAttachment::SOURCE_ANONYMOUS)
                            by anonymous submitter
                        @endif
                    </p>
                </div>

                @if ($canDownloadAttachments)
                    <a href="{{ route('case-attachments.download', $attachment) }}" class="inline-flex w-fit justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Download
                    </a>
                @endif
            </div>
        @empty
            <p class="rounded-md bg-gray-50 p-4 text-sm text-gray-600">No attachments have been uploaded for this case.</p>
        @endforelse
    </div>

    @if ($canUploadAttachments && $uploadAction)
        <form method="POST" action="{{ $uploadAction }}" enctype="multipart/form-data" class="mt-6 border-t border-gray-200 pt-5">
            @csrf

            <x-input-label for="attachments-upload-{{ $case->case_reference }}" value="Upload Attachments" />
            <input id="attachments-upload-{{ $case->case_reference }}" name="attachments[]" type="file" multiple accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" class="mt-2 block w-full rounded-md border border-gray-300 text-sm text-gray-700 file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-gray-700 hover:file:bg-gray-200" />
            <x-input-error :messages="$errors->get('attachments')" class="mt-2" />
            <x-input-error :messages="$errors->get('attachments.*')" class="mt-2" />

            <x-primary-button class="mt-4">Upload Attachment</x-primary-button>
        </form>
    @elseif ($case->status === \App\Models\SupportCase::STATUS_CLOSED)
        <p class="mt-5 rounded-md border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600">This case is closed, so new attachment uploads are disabled.</p>
    @endif
</section>
