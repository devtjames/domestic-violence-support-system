<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AnonymousCaseAccess extends Model
{
    protected $fillable = [
        'support_case_id',
        'access_code_hash',
        'last_accessed_at',
    ];

    protected $hidden = [
        'access_code_hash',
    ];

    protected function casts(): array
    {
        return [
            'last_accessed_at' => 'datetime',
        ];
    }

    public static function generateAccessCode(int $length = 8): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';

        for ($index = 0; $index < $length; $index++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $code;
    }

    public static function hashAccessCode(string $accessCode): string
    {
        return Hash::make(Str::upper($accessCode));
    }

    public function matches(string $accessCode): bool
    {
        return Hash::check(Str::upper($accessCode), $this->access_code_hash);
    }

    /**
     * @return BelongsTo<SupportCase, $this>
     */
    public function supportCase(): BelongsTo
    {
        return $this->belongsTo(SupportCase::class);
    }
}
