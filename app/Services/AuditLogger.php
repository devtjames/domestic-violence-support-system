<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        ?Request $request,
        string $module,
        string $action,
        string $description,
        ?Model $auditable = null,
        array $metadata = []
    ): AuditLog {
        $actor = $request?->user();

        return AuditLog::query()->create([
            'user_id' => $actor?->id,
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $metadata === [] ? null : $metadata,
        ]);
    }
}
