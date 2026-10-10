<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFaqRequest;
use App\Http\Requests\Admin\UpdateFaqRequest;
use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(Request $request): View
    {
        $faqs = Faq::query()
            ->with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('question', 'like', "%{$search}%")
                        ->orWhere('answer', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('faq_category_id', $request->integer('category')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->orderBy('faq_category_id')
            ->orderBy('sort_order')
            ->orderBy('question')
            ->paginate(10)
            ->withQueryString();

        return view('admin.faqs.index', [
            'faqs' => $faqs,
            'categories' => FaqCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
            'statuses' => Faq::statuses(),
        ]);
    }

    public function create(): View
    {
        return view('admin.faqs.create', [
            'faq' => new Faq([
                'status' => Faq::STATUS_ACTIVE,
                'sort_order' => Faq::query()->max('sort_order') + 1,
            ]),
            'categories' => FaqCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
            'statuses' => Faq::statuses(),
        ]);
    }

    public function store(StoreFaqRequest $request): RedirectResponse
    {
        $faq = Faq::create($request->validated());

        return redirect()
            ->route('admin.faqs.edit', $faq)
            ->with('status', 'FAQ created.');
    }

    public function edit(Faq $faq): View
    {
        return view('admin.faqs.edit', [
            'faq' => $faq,
            'categories' => FaqCategory::query()->orderBy('sort_order')->orderBy('name')->get(),
            'statuses' => Faq::statuses(),
        ]);
    }

    public function update(UpdateFaqRequest $request, Faq $faq): RedirectResponse
    {
        $faq->update($request->validated());

        return redirect()
            ->route('admin.faqs.edit', $faq)
            ->with('status', 'FAQ updated.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()
            ->route('admin.faqs.index')
            ->with('status', 'FAQ deleted.');
    }

    public function activate(Faq $faq): RedirectResponse
    {
        $faq->update(['status' => Faq::STATUS_ACTIVE]);

        return back()->with('status', 'FAQ activated.');
    }

    public function deactivate(Faq $faq): RedirectResponse
    {
        $faq->update(['status' => Faq::STATUS_INACTIVE]);

        return back()->with('status', 'FAQ deactivated.');
    }

    public function moveUp(Faq $faq): RedirectResponse
    {
        $previous = Faq::query()
            ->where('faq_category_id', $faq->faq_category_id)
            ->where('sort_order', '<', $faq->sort_order)
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->first();

        $this->swapSortOrder($faq, $previous);

        return back()->with('status', 'FAQ order updated.');
    }

    public function moveDown(Faq $faq): RedirectResponse
    {
        $next = Faq::query()
            ->where('faq_category_id', $faq->faq_category_id)
            ->where('sort_order', '>', $faq->sort_order)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        $this->swapSortOrder($faq, $next);

        return back()->with('status', 'FAQ order updated.');
    }

    private function swapSortOrder(Faq $faq, ?Faq $otherFaq): void
    {
        if (! $otherFaq) {
            return;
        }

        $currentOrder = $faq->sort_order;

        $faq->update(['sort_order' => $otherFaq->sort_order]);
        $otherFaq->update(['sort_order' => $currentOrder]);
    }
}
