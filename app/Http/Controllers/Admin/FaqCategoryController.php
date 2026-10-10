<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFaqCategoryRequest;
use App\Http\Requests\Admin\UpdateFaqCategoryRequest;
use App\Models\FaqCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = FaqCategory::query()
            ->withCount('faqs')
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

        return view('admin.faqs.categories.index', [
            'categories' => $categories,
            'statuses' => FaqCategory::statuses(),
        ]);
    }

    public function create(): View
    {
        return view('admin.faqs.categories.create', [
            'category' => new FaqCategory([
                'status' => FaqCategory::STATUS_ACTIVE,
                'sort_order' => FaqCategory::query()->max('sort_order') + 1,
            ]),
            'statuses' => FaqCategory::statuses(),
        ]);
    }

    public function store(StoreFaqCategoryRequest $request): RedirectResponse
    {
        $category = FaqCategory::create($request->validated());

        return redirect()
            ->route('admin.faq-categories.edit', $category)
            ->with('status', 'FAQ category created.');
    }

    public function edit(FaqCategory $faqCategory): View
    {
        return view('admin.faqs.categories.edit', [
            'category' => $faqCategory,
            'statuses' => FaqCategory::statuses(),
        ]);
    }

    public function update(UpdateFaqCategoryRequest $request, FaqCategory $faqCategory): RedirectResponse
    {
        $faqCategory->update($request->validated());

        return redirect()
            ->route('admin.faq-categories.edit', $faqCategory)
            ->with('status', 'FAQ category updated.');
    }

    public function destroy(FaqCategory $faqCategory): RedirectResponse
    {
        if ($faqCategory->faqs()->withTrashed()->exists()) {
            return back()->withErrors(['category' => 'Move or delete FAQs in this category before deleting it.']);
        }

        $faqCategory->delete();

        return redirect()
            ->route('admin.faq-categories.index')
            ->with('status', 'FAQ category deleted.');
    }

    public function activate(FaqCategory $faqCategory): RedirectResponse
    {
        $faqCategory->update(['status' => FaqCategory::STATUS_ACTIVE]);

        return back()->with('status', 'FAQ category activated.');
    }

    public function deactivate(FaqCategory $faqCategory): RedirectResponse
    {
        $faqCategory->update(['status' => FaqCategory::STATUS_INACTIVE]);

        return back()->with('status', 'FAQ category deactivated.');
    }

    public function moveUp(FaqCategory $faqCategory): RedirectResponse
    {
        $previous = FaqCategory::query()
            ->where('sort_order', '<', $faqCategory->sort_order)
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->first();

        $this->swapSortOrder($faqCategory, $previous);

        return back()->with('status', 'FAQ category order updated.');
    }

    public function moveDown(FaqCategory $faqCategory): RedirectResponse
    {
        $next = FaqCategory::query()
            ->where('sort_order', '>', $faqCategory->sort_order)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        $this->swapSortOrder($faqCategory, $next);

        return back()->with('status', 'FAQ category order updated.');
    }

    private function swapSortOrder(FaqCategory $category, ?FaqCategory $otherCategory): void
    {
        if (! $otherCategory) {
            return;
        }

        $currentOrder = $category->sort_order;

        $category->update(['sort_order' => $otherCategory->sort_order]);
        $otherCategory->update(['sort_order' => $currentOrder]);
    }
}
