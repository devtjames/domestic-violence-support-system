<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSystemSettingsRequest;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => SystemSetting::allAsArray(),
        ]);
    }

    public function update(UpdateSystemSettingsRequest $request): RedirectResponse
    {
        $before = SystemSetting::allAsArray();
        $validated = $request->validated();

        SystemSetting::setMany($validated);

        $changedKeys = collect($validated)
            ->filter(fn ($value, $key): bool => ($before[$key] ?? null) !== (filled($value) ? (string) $value : null))
            ->keys()
            ->values()
            ->all();

        if ($changedKeys !== []) {
            app(AuditLogger::class)->record(
                $request,
                AuditLog::MODULE_SETTINGS,
                'settings_updated',
                'System settings updated.',
                null,
                ['changed_settings' => $changedKeys]
            );
        }

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'System settings updated.');
    }
}
