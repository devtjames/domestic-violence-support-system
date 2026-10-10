<?php

namespace App\Models;

use Database\Factories\SupportCaseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use RuntimeException;

class SupportCase extends Model
{
    /** @use HasFactory<SupportCaseFactory> */
    use HasFactory, SoftDeletes;

    public const URGENCY_LOW = 'low';

    public const URGENCY_MEDIUM = 'medium';

    public const URGENCY_HIGH = 'high';

    public const URGENCY_CRITICAL = 'critical';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_UNDER_REVIEW = 'under_review';

    public const STATUS_ASSIGNED = 'assigned';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_AWAITING_USER = 'awaiting_user';

    public const STATUS_REFERRED = 'referred';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_CLOSED = 'closed';

    public const CONTACT_PLATFORM_MESSAGE = 'platform_message';

    public const CONTACT_EMAIL = 'email';

    public const CONTACT_PHONE = 'phone';

    public const CONTACT_NO_DIRECT_CONTACT = 'no_direct_contact';

    protected $fillable = [
        'case_reference',
        'user_id',
        'is_anonymous',
        'anonymous_nickname',
        'abuse_type_id',
        'subject',
        'description',
        'preferred_contact_method',
        'contact_email',
        'contact_phone',
        'safe_contact_time',
        'safe_contact_instruction',
        'urgency',
        'status',
        'assigned_officer_id',
        'resolution_summary',
        'resolved_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SupportCase $supportCase) {
            if (blank($supportCase->case_reference)) {
                $supportCase->case_reference = static::generateCaseReference();
            }
        });
    }

    public static function urgencies(): array
    {
        return [
            self::URGENCY_LOW,
            self::URGENCY_MEDIUM,
            self::URGENCY_HIGH,
            self::URGENCY_CRITICAL,
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_SUBMITTED,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_ASSIGNED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_AWAITING_USER,
            self::STATUS_REFERRED,
            self::STATUS_RESOLVED,
            self::STATUS_CLOSED,
        ];
    }

    public static function contactMethods(): array
    {
        return [
            self::CONTACT_PLATFORM_MESSAGE => 'Platform message',
            self::CONTACT_EMAIL => 'Email',
            self::CONTACT_PHONE => 'Phone',
            self::CONTACT_NO_DIRECT_CONTACT => 'No direct contact',
        ];
    }

    public static function openStatuses(): array
    {
        return [
            self::STATUS_SUBMITTED,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_ASSIGNED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_AWAITING_USER,
            self::STATUS_REFERRED,
        ];
    }

    public static function terminalStatuses(): array
    {
        return [
            self::STATUS_RESOLVED,
            self::STATUS_CLOSED,
        ];
    }

    public static function isOpenStatus(string $status): bool
    {
        return in_array($status, self::openStatuses(), true);
    }

    public static function isTerminalStatus(string $status): bool
    {
        return in_array($status, self::terminalStatuses(), true);
    }

    public static function generateCaseReference(): string
    {
        $year = now()->year;
        $casePrefix = SystemSetting::caseReferencePrefix();
        $prefix = "{$casePrefix}-{$year}-";
        $latestReference = static::withTrashed()
            ->where('case_reference', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('case_reference');

        $nextNumber = 1;

        $pattern = '/^'.preg_quote($casePrefix, '/').'-\d{4}-(\d{6})$/';

        if ($latestReference && preg_match($pattern, $latestReference, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        for ($number = $nextNumber; $number < $nextNumber + 1000; $number++) {
            $reference = sprintf('%s%06d', $prefix, $number);

            if (! static::withTrashed()->where('case_reference', $reference)->exists()) {
                return $reference;
            }
        }

        throw new RuntimeException('Unable to generate a unique support case reference.');
    }

    public function getRouteKeyName(): string
    {
        return 'case_reference';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<AbuseType, $this>
     */
    public function abuseType(): BelongsTo
    {
        return $this->belongsTo(AbuseType::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_officer_id');
    }

    /**
     * @return HasOne<AnonymousCaseAccess, $this>
     */
    public function anonymousAccess(): HasOne
    {
        return $this->hasOne(AnonymousCaseAccess::class);
    }

    /**
     * @return HasMany<CaseActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(CaseActivity::class)->latest();
    }

    /**
     * @return HasMany<CaseMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(CaseMessage::class)->oldest();
    }

    /**
     * @return HasMany<CaseAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(CaseAttachment::class)->latest();
    }

    /**
     * @return HasMany<CaseNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(CaseNote::class)->latest();
    }

    public function markMessagesReadFor(User $user): int
    {
        return CaseMessage::query()
            ->where('support_case_id', $this->id)
            ->unreadFor($user)
            ->update(['read_at' => now()]);
    }

    public function canBeManagedByOfficer(User $officer): bool
    {
        if (! $officer->isSupportOfficer()) {
            return false;
        }

        return $this->assigned_officer_id === $officer->id
            || ($this->assigned_officer_id === null && self::isOpenStatus($this->status));
    }

    public function recordActivity(
        string $actionType,
        string $description,
        ?User $actor = null,
        ?string $previousValue = null,
        ?string $newValue = null,
        ?Request $request = null
    ): CaseActivity {
        return $this->activities()->create([
            'user_id' => $actor?->id,
            'action_type' => $actionType,
            'description' => $description,
            'previous_value' => $previousValue,
            'new_value' => $newValue,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    /**
     * @param  Builder<SupportCase>  $query
     */
    public function scopeOwnedBy(Builder $query, User $user): void
    {
        $query->where('user_id', $user->id);
    }

    /**
     * @param  Builder<SupportCase>  $query
     */
    public function scopeVisibleToOfficer(Builder $query, User $officer): void
    {
        $query->where(function ($query) use ($officer) {
            $query
                ->where('assigned_officer_id', $officer->id)
                ->orWhere(function ($query) {
                    $query
                        ->whereNull('assigned_officer_id')
                        ->whereIn('status', self::openStatuses());
                });
        });
    }
}
