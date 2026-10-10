<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuditLogFilterRequest;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(AuditLogFilterRequest $request): View
    {
        $sort = $request->string('sort')->toString();
        $sort = in_array($sort, ['newest', 'oldest', 'module', 'action'], true) ? $sort : 'newest';

        $query = AuditLog::query()
            ->with('actor:id,name,email,role')
            ->when($request->filled('module'), fn ($query) => $query->where('module', $request->string('module')->toString()))
            ->when($request->filled('action'), fn ($query) => $query->where('action', 'like', '%'.$request->string('action')->toString().'%'))
            ->when($request->filled('actor'), fn ($query) => $query->where('user_id', $request->integer('actor')))
            ->when($request->filled('date_from'), fn ($query) => $query->where('created_at', '>=', CarbonImmutable::parse($request->date('date_from'))->startOfDay()))
            ->when($request->filled('date_to'), fn ($query) => $query->where('created_at', '<=', CarbonImmutable::parse($request->date('date_to'))->endOfDay()))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('description', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('module', 'like', "%{$search}%");
                });
            });

        match ($sort) {
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'module' => $query->orderBy('module')->orderByDesc('created_at'),
            'action' => $query->orderBy('action')->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };

        $logs = $query
            ->paginate(15)
            ->withQueryString();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'modules' => AuditLog::modules(),
            'actors' => User::query()
                ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPPORT_OFFICER])
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role']),
            'sortOptions' => [
                'newest' => 'Newest first',
                'oldest' => 'Oldest first',
                'module' => 'Module',
                'action' => 'Action',
            ],
        ]);
    }

    public function show(AuditLog $auditLog): View
    {
        return view('admin.audit-logs.show', [
            'log' => $auditLog->load('actor:id,name,email,role'),
        ]);
    }
}
