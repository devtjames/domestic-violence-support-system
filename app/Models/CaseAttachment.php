<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CaseAttachment extends Model
{
    public const MAX_FILES = 3;

    public const MAX_UPLOAD_KB = 5120;

    public const SOURCE_REGISTERED_USER = 'registered_user';

    public const SOURCE_ANONYMOUS = 'anonymous';

    public const SOURCE_SUPPORT_OFFICER = 'support_officer';

    public const SOURCE_ADMIN = 'admin';

    protected $fillable = [
        'public_id',
        'support_case_id',
        'uploaded_by_user_id',
        'upload_source',
        'disk',
        'path',
        'stored_name',
        'original_name',
        'mime_type',
        'extension',
        'size',
    ];

    protected static function booted(): void
    {
        static::creating(function (CaseAttachment $attachment) {
            if (blank($attachment->public_id)) {
                $attachment->public_id = (string) Str::uuid();
            }
        });
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function uploadRules(bool $required = false): array
    {
        return [
            'attachments' => [
                $required ? 'required' : 'nullable',
                'array',
                'max:'.self::MAX_FILES,
            ],
            'attachments.*' => [
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'mimetypes:image/jpeg,image/png,application/pdf',
                'max:'.self::MAX_UPLOAD_KB,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function uploadMessages(): array
    {
        $megabytes = self::MAX_UPLOAD_KB / 1024;

        return [
            'attachments.required' => 'Choose at least one attachment to upload.',
            'attachments.array' => 'Upload attachments as separate files.',
            'attachments.max' => 'Upload no more than '.self::MAX_FILES.' attachments at a time.',
            'attachments.*.file' => 'Each attachment must be a valid file.',
            'attachments.*.mimes' => 'Attachments must be JPG, JPEG, PNG, or PDF files.',
            'attachments.*.mimetypes' => 'Attachments must be JPG, JPEG, PNG, or PDF files.',
            'attachments.*.max' => 'Each attachment must be '.$megabytes.' MB or smaller.',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function formattedSize(): string
    {
        if ($this->size >= 1048576) {
            return number_format($this->size / 1048576, 1).' MB';
        }

        return number_format(max(1, $this->size / 1024), 1).' KB';
    }

    public function safeDownloadName(): string
    {
        return sprintf(
            'support-case-%s-attachment-%s.%s',
            $this->supportCase?->case_reference ?? 'file',
            Str::of($this->public_id)->before('-'),
            $this->extension
        );
    }

    /**
     * @return BelongsTo<SupportCase, $this>
     */
    public function supportCase(): BelongsTo
    {
        return $this->belongsTo(SupportCase::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
