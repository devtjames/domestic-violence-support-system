<?php

namespace App\Models;

use Database\Factories\SupportResourceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportResource extends Model
{
    /** @use HasFactory<SupportResourceFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const TYPE_COUNSELLING = 'Counselling';

    public const TYPE_SHELTER = 'Shelter';

    public const TYPE_MEDICAL_SUPPORT = 'Medical Support';

    public const TYPE_LEGAL_SUPPORT = 'Legal Support';

    public const TYPE_COMMUNITY_SUPPORT = 'Community Support';

    public const TYPE_EMERGENCY_SUPPORT = 'Emergency Support';

    public const TYPE_CHILD_PROTECTION = 'Child Protection';

    public const TYPE_GENERAL_SUPPORT = 'General Support';

    protected $fillable = [
        'organization_name',
        'resource_type',
        'description',
        'phone',
        'email',
        'website',
        'address',
        'location',
        'availability',
        'status',
    ];

    public static function resourceTypes(): array
    {
        return [
            self::TYPE_COUNSELLING,
            self::TYPE_SHELTER,
            self::TYPE_MEDICAL_SUPPORT,
            self::TYPE_LEGAL_SUPPORT,
            self::TYPE_COMMUNITY_SUPPORT,
            self::TYPE_EMERGENCY_SUPPORT,
            self::TYPE_CHILD_PROTECTION,
            self::TYPE_GENERAL_SUPPORT,
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
        ];
    }

    /**
     * @param  Builder<SupportResource>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE);
    }
}
