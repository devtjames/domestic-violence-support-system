<?php

namespace App\Models;

use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_NEW = 'new';

    public const STATUS_READ = 'read';

    public const STATUS_RESPONDED = 'responded';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
        'admin_note',
        'reviewed_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_READ,
            self::STATUS_RESPONDED,
            self::STATUS_RESOLVED,
            self::STATUS_ARCHIVED,
        ];
    }

    /**
     * @param  Builder<ContactMessage>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereIn('status', [
            self::STATUS_NEW,
            self::STATUS_READ,
            self::STATUS_RESPONDED,
        ]);
    }
}
