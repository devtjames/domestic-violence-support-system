<?php

namespace App\Models;

use Database\Factories\CaseMessageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseMessage extends Model
{
    /** @use HasFactory<CaseMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'support_case_id',
        'sender_id',
        'receiver_id',
        'message',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
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
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * @param  Builder<CaseMessage>  $query
     */
    public function scopeUnreadFor(Builder $query, User $user): void
    {
        $query
            ->where('receiver_id', $user->id)
            ->whereNull('read_at');
    }
}
