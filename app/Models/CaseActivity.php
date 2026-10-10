<?php

namespace App\Models;

use Database\Factories\CaseActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseActivity extends Model
{
    /** @use HasFactory<CaseActivityFactory> */
    use HasFactory;

    public const ACTION_CASE_SUBMITTED = 'case_submitted';

    public const ACTION_CASE_ASSIGNED = 'case_assigned';

    public const ACTION_CASE_REASSIGNED = 'case_reassigned';

    public const ACTION_STATUS_CHANGED = 'status_changed';

    public const ACTION_URGENCY_CHANGED = 'urgency_changed';

    public const ACTION_CASE_REFERRED = 'case_referred';

    public const ACTION_CASE_RESOLVED = 'case_resolved';

    public const ACTION_CASE_CLOSED = 'case_closed';

    public const ACTION_CASE_REOPENED = 'case_reopened';

    public const ACTION_CASE_UPDATED = 'case_updated';

    public const ACTION_USER_RESPONDED = 'user_responded';

    public const ACTION_OFFICER_RESPONDED = 'officer_responded';

    public const ACTION_INTERNAL_NOTE_ADDED = 'internal_note_added';

    public const ACTION_ATTACHMENT_ADDED = 'attachment_added';

    protected $fillable = [
        'support_case_id',
        'user_id',
        'action_type',
        'description',
        'previous_value',
        'new_value',
        'ip_address',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::ACTION_CASE_SUBMITTED => 'Case submitted',
            self::ACTION_CASE_ASSIGNED => 'Case assigned',
            self::ACTION_CASE_REASSIGNED => 'Case reassigned',
            self::ACTION_STATUS_CHANGED => 'Status changed',
            self::ACTION_URGENCY_CHANGED => 'Urgency changed',
            self::ACTION_CASE_REFERRED => 'Case referred',
            self::ACTION_CASE_RESOLVED => 'Case resolved',
            self::ACTION_CASE_CLOSED => 'Case closed',
            self::ACTION_CASE_REOPENED => 'Case reopened',
            self::ACTION_CASE_UPDATED => 'Case updated',
            self::ACTION_USER_RESPONDED => 'User responded',
            self::ACTION_OFFICER_RESPONDED => 'Officer responded',
            self::ACTION_INTERNAL_NOTE_ADDED => 'Internal note added',
            self::ACTION_ATTACHMENT_ADDED => 'Attachment added',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->action_type] ?? str($this->action_type)->headline()->toString();
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
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
