<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_USER = 'user';

    public const ROLE_SUPPORT_OFFICER = 'support_officer';

    public const ROLE_ADMIN = 'admin';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_DISABLED = 'disabled';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'staff_id',
        'specialization',
        'date_joined',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_joined' => 'date',
            'password' => 'hashed',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function roles(): array
    {
        return [
            self::ROLE_USER,
            self::ROLE_SUPPORT_OFFICER,
            self::ROLE_ADMIN,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function accountStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_DISABLED,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function supportOfficerSpecializations(): array
    {
        return [
            'General Support',
            'Counselling',
            'Legal Support',
            'Child Protection',
            'Social Support',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function genders(): array
    {
        return [
            'female',
            'male',
            'non_binary',
            'prefer_not_to_say',
        ];
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isActive(): bool
    {
        return $this->account_status === self::STATUS_ACTIVE;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isSupportOfficer(): bool
    {
        return $this->hasRole(self::ROLE_SUPPORT_OFFICER);
    }

    public function isRegisteredUser(): bool
    {
        return $this->hasRole(self::ROLE_USER);
    }

    public function dashboardRouteName(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'admin.dashboard',
            self::ROLE_SUPPORT_OFFICER => 'officer.dashboard',
            default => 'dashboard',
        };
    }

    /**
     * @return HasMany<SupportCase, $this>
     */
    public function supportCases(): HasMany
    {
        return $this->hasMany(SupportCase::class);
    }

    /**
     * @return HasMany<SupportCase, $this>
     */
    public function assignedSupportCases(): HasMany
    {
        return $this->hasMany(SupportCase::class, 'assigned_officer_id');
    }

    /**
     * @return HasMany<CaseMessage, $this>
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(CaseMessage::class, 'sender_id');
    }

    /**
     * @return HasMany<CaseMessage, $this>
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(CaseMessage::class, 'receiver_id');
    }

    /**
     * @return HasMany<CaseNote, $this>
     */
    public function caseNotes(): HasMany
    {
        return $this->hasMany(CaseNote::class);
    }
}
