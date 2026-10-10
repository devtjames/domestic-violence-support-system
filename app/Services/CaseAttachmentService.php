<?php

namespace App\Services;

use App\Models\CaseAttachment;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class CaseAttachmentService
{
    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, CaseAttachment>
     */
    public function storeMany(SupportCase $supportCase, array $files, ?User $uploader = null): array
    {
        $attachments = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $attachments[] = $this->store($supportCase, $file, $uploader);
        }

        return $attachments;
    }

    public function store(SupportCase $supportCase, UploadedFile $file, ?User $uploader = null): CaseAttachment
    {
        $disk = 'local';
        $extension = $this->extensionFor($file);
        $storedName = (string) Str::uuid().'.'.$extension;
        $directory = 'case-attachments/'.$supportCase->case_reference;
        $path = $file->storeAs($directory, $storedName, $disk);

        if (! is_string($path)) {
            throw new RuntimeException('Unable to store case attachment.');
        }

        return $supportCase->attachments()->create([
            'uploaded_by_user_id' => $uploader?->id,
            'upload_source' => $this->sourceFor($supportCase, $uploader),
            'disk' => $disk,
            'path' => $path,
            'stored_name' => $storedName,
            'original_name' => basename($file->getClientOriginalName()),
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'extension' => $extension,
            'size' => $file->getSize() ?: Storage::disk($disk)->size($path),
        ]);
    }

    private function extensionFor(UploadedFile $file): string
    {
        return match ($file->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'application/pdf' => 'pdf',
            default => strtolower($file->extension() ?: 'bin'),
        };
    }

    private function sourceFor(SupportCase $supportCase, ?User $uploader): string
    {
        if ($uploader?->isAdmin()) {
            return CaseAttachment::SOURCE_ADMIN;
        }

        if ($uploader?->isSupportOfficer()) {
            return CaseAttachment::SOURCE_SUPPORT_OFFICER;
        }

        if ($supportCase->is_anonymous) {
            return CaseAttachment::SOURCE_ANONYMOUS;
        }

        return CaseAttachment::SOURCE_REGISTERED_USER;
    }
}
