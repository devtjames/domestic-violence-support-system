<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSupportResourceRequest;
use App\Http\Requests\Admin\UpdateSupportResourceRequest;
use App\Models\AuditLog;
use App\Models\SupportResource;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportResourceController extends Controller
{
    public function index(Request $request): View
    {
        $sort = $this->normalizedSort($request->string('sort')->toString(), $this->resourceSortOptions());

        $query = SupportResource::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('organization_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('resource_type'), fn ($query) => $query->where('resource_type', $request->string('resource_type')->toString()))
            ->when($request->filled('location'), fn ($query) => $query->where('location', 'like', '%'.$request->string('location')->toString().'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()));

        $this->applyResourceSort($query, $sort);

        $resources = $query
            ->paginate(10)
            ->withQueryString();

        return view('admin.resources.index', [
            'resources' => $resources,
            'resourceTypes' => SupportResource::resourceTypes(),
            'statuses' => SupportResource::statuses(),
            'sortOptions' => $this->resourceSortOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.resources.create', [
            'resource' => new SupportResource([
                'status' => SupportResource::STATUS_ACTIVE,
                'availability' => 'Configurable by administrator',
            ]),
            'resourceTypes' => SupportResource::resourceTypes(),
            'statuses' => SupportResource::statuses(),
        ]);
    }

    public function store(StoreSupportResourceRequest $request): RedirectResponse
    {
        $resource = SupportResource::create($request->validated());

        app(AuditLogger::class)->record(
            $request,
            AuditLog::MODULE_RESOURCES,
            'resource_created',
            'Support resource created.',
            $resource,
            ['resource_type' => $resource->resource_type, 'status' => $resource->status]
        );

        return redirect()
            ->route('admin.resources.edit', $resource)
            ->with('status', 'Support resource created.');
    }

    public function show(SupportResource $supportResource): View
    {
        return view('admin.resources.show', [
            'resource' => $supportResource,
        ]);
    }

    public function edit(SupportResource $supportResource): View
    {
        return view('admin.resources.edit', [
            'resource' => $supportResource,
            'resourceTypes' => SupportResource::resourceTypes(),
            'statuses' => SupportResource::statuses(),
        ]);
    }

    public function update(UpdateSupportResourceRequest $request, SupportResource $supportResource): RedirectResponse
    {
        $previousStatus = $supportResource->status;
        $previousType = $supportResource->resource_type;

        $supportResource->update($request->validated());

        app(AuditLogger::class)->record(
            $request,
            AuditLog::MODULE_RESOURCES,
            'resource_updated',
            'Support resource updated.',
            $supportResource,
            [
                'previous_status' => $previousStatus,
                'new_status' => $supportResource->status,
                'previous_type' => $previousType,
                'new_type' => $supportResource->resource_type,
            ]
        );

        return redirect()
            ->route('admin.resources.edit', $supportResource)
            ->with('status', 'Support resource updated.');
    }

    public function destroy(SupportResource $supportResource): RedirectResponse
    {
        app(AuditLogger::class)->record(
            request(),
            AuditLog::MODULE_RESOURCES,
            'resource_deleted',
            'Support resource deleted.',
            $supportResource,
            ['resource_type' => $supportResource->resource_type, 'status' => $supportResource->status]
        );

        $supportResource->delete();

        return redirect()
            ->route('admin.resources.index')
            ->with('status', 'Support resource deleted.');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<SupportResource>  $query
     */
    private function applyResourceSort($query, string $sort): void
    {
        match ($sort) {
            'newest' => $query->orderByDesc('created_at')->orderByDesc('id'),
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'type' => $query->orderBy('resource_type')->orderBy('organization_name'),
            'location' => $query->orderBy('location')->orderBy('organization_name'),
            'status' => $query->orderBy('status')->orderBy('organization_name'),
            default => $query->orderBy('organization_name'),
        };
    }

    /**
     * @return array<string, string>
     */
    private function resourceSortOptions(): array
    {
        return [
            'organization' => 'Organization',
            'newest' => 'Newest added',
            'oldest' => 'Oldest added',
            'type' => 'Resource type',
            'location' => 'Location',
            'status' => 'Status',
        ];
    }

    /**
     * @param  array<string, string>  $options
     */
    private function normalizedSort(string $sort, array $options): string
    {
        return array_key_exists($sort, $options) ? $sort : 'organization';
    }
}
