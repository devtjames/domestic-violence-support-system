<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    public const MODULE_AUTH = 'auth';

    public const MODULE_USERS = 'users';

    public const MODULE_SUPPORT_OFFICERS = 'support_officers';

    public const MODULE_CASES = 'cases';

    public const MODULE_AWARENESS = 'awareness';

    public const MODULE_ABUSE_TYPES = 'abuse_types';

    public const MODULE_RESOURCES = 'resources';

    public const MODULE_CONTACT_MESSAGES = 'contact_messages';

    public const MODULE_FAQS = 'faqs';

    public const MODULE_SETTINGS = 'settings';

    protected $fillable = [
        'user_id',
        'module',
        'action',
        'description',
        'auditable_type',
        'auditable_id',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function modules(): array
    {
        return [
            self::MODULE_AUTH,
            self::MODULE_USERS,
            self::MODULE_SUPPORT_OFFICERS,
            self::MODULE_CASES,
            self::MODULE_AWARENESS,
            self::MODULE_ABUSE_TYPES,
            self::MODULE_RESOURCES,
            self::MODULE_CONTACT_MESSAGES,
            self::MODULE_FAQS,
            self::MODULE_SETTINGS,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
