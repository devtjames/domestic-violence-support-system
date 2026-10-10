<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAbuseTypeRequest;
use App\Http\Requests\Admin\UpdateAbuseTypeRequest;
use App\Models\AbuseType;
use App\Models\AuditLog;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AbuseTypeController extends Controller
{
    public function index(Request $request): View
    {
        $abuseTypes = AbuseType::query()
            ->withCount('supportCases')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.abuse-types.index', [
            'abuseTypes' => $abuseTypes,
            'statuses' => AbuseType::statuses(),
        ]);
    }

    public function create(): View
    {
        return view('admin.abuse-types.create', [
            'abuseType' => new AbuseType([
                'status' => AbuseType::STATUS_ACTIVE,
                'sort_order' => AbuseType::query()->max('sort_order') + 1,
            ]),
            'statuses' => AbuseType::statuses(),
        ]);
    }

    public function store(StoreAbuseTypeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $abuseType = AbuseType::create($data);

        app(AuditLogger::class)->record(
            $request,
            AuditLog::MODULE_ABUSE_TYPES,
            'abuse_type_created',
            'Abuse type created.',
            $abuseType,
            ['name' => $abuseType->name, 'status' => $abuseType->status]
        );

        return redirect()
            ->route('admin.abuse-types.edit', $abuseType)
            ->with('status', 'Abuse type created.');
    }

    public function edit(AbuseType $abuseType): View
    {
        return view('admin.abuse-types.edit', [
            'abuseType' => $abuseType,
            'statuses' => AbuseType::statuses(),
        ]);
    }

    public function update(UpdateAbuseTypeRequest $request, AbuseType $abuseType): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $previousStatus = $abuseType->status;

        $abuseType->update($data);

        app(AuditLogger::class)->record(
            $request,
            AuditLog::MODULE_ABUSE_TYPES,
            'abuse_type_updated',
            'Abuse type updated.',
            $abuseType,
            [
                'name' => $abuseType->name,
                'previous_status' => $previousStatus,
                'new_status' => $abuseType->status,
            ]
        );

        return redirect()
            ->route('admin.abuse-types.edit', $abuseType)
            ->with('status', 'Abuse type updated.');
    }

    public function destroy(Request $request, AbuseType $abuseType): RedirectResponse
    {
        if ($abuseType->supportCases()->withTrashed()->exists()) {
            return back()->withErrors([
                'abuse_type' => 'Deactivate this abuse type instead. Existing cases must keep their historical category.',
            ]);
        }

        $abuseType->delete();

        app(AuditLogger::class)->record(
            $request,
            AuditLog::MODULE_ABUSE_TYPES,
            'abuse_type_deleted',
            'Unused abuse type deleted.',
            $abuseType,
            ['name' => $abuseType->name]
        );

        return redirect()
            ->route('admin.abuse-types.index')
            ->with('status', 'Abuse type deleted.');
    }

    public function activate(Request $request, AbuseType $abuseType): RedirectResponse
    {
        $abuseType->update(['status' => AbuseType::STATUS_ACTIVE]);

        app(AuditLogger::class)->record(
            $request,
            AuditLog::MODULE_ABUSE_TYPES,
            'abuse_type_activated',
            'Abuse type activated.',
            $abuseType,
            ['name' => $abuseType->name]
        );

        return back()->with('status', 'Abuse type activated.');
    }

    public function deactivate(Request $request, AbuseType $abuseType): RedirectResponse
    {
        $abuseType->update(['status' => AbuseType::STATUS_INACTIVE]);

        app(AuditLogger::class)->record(
            $request,
            AuditLog::MODULE_ABUSE_TYPES,
            'abuse_type_deactivated',
            'Abuse type deactivated.',
            $abuseType,
            ['name' => $abuseType->name]
        );

        return back()->with('status', 'Abuse type deactivated.');
    }
}
