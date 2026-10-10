<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAwarenessCategoryRequest;
use App\Http\Requests\Admin\UpdateAwarenessCategoryRequest;
use App\Models\AwarenessCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AwarenessCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = AwarenessCategory::query()
            ->withCount('articles')
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

        return view('admin.awareness.categories.index', [
            'categories' => $categories,
            'statuses' => AwarenessCategory::statuses(),
        ]);
    }

    public function create(): View
    {
        return view('admin.awareness.categories.create', [
            'category' => new AwarenessCategory([
                'status' => AwarenessCategory::STATUS_ACTIVE,
                'sort_order' => 0,
            ]),
            'statuses' => AwarenessCategory::statuses(),
        ]);
    }

    public function store(StoreAwarenessCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = $data['sort_order'] ?? 0;

        AwarenessCategory::create($data);

        return redirect()
            ->route('admin.awareness.categories.index')
            ->with('status', 'Awareness category created.');
    }

    public function show(AwarenessCategory $awarenessCategory): RedirectResponse
    {
        return redirect()->route('admin.awareness.categories.edit', $awarenessCategory);
    }

    public function edit(AwarenessCategory $awarenessCategory): View
    {
        return view('admin.awareness.categories.edit', [
            'category' => $awarenessCategory,
            'statuses' => AwarenessCategory::statuses(),
        ]);
    }

    public function update(UpdateAwarenessCategoryRequest $request, AwarenessCategory $awarenessCategory): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $awarenessCategory->update($data);

        return redirect()
            ->route('admin.awareness.categories.index')
            ->with('status', 'Awareness category updated.');
    }

    public function destroy(AwarenessCategory $awarenessCategory): RedirectResponse
    {
        if ($awarenessCategory->articles()->exists()) {
            return back()->withErrors([
                'category' => 'Archive or move articles before deleting this category.',
            ]);
        }

        $awarenessCategory->delete();

        return redirect()
            ->route('admin.awareness.categories.index')
            ->with('status', 'Awareness category deleted.');
    }
}
