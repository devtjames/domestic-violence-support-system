<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAwarenessArticleRequest;
use App\Http\Requests\Admin\UpdateAwarenessArticleRequest;
use App\Models\AuditLog;
use App\Models\AwarenessArticle;
use App\Models\AwarenessCategory;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AwarenessArticleController extends Controller
{
    public function index(Request $request): View
    {
        $sort = $this->normalizedSort($request->string('sort')->toString(), $this->articleSortOptions());

        $query = AwarenessArticle::query()
            ->with(['category', 'author'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('awareness_category_id', $request->integer('category')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()));

        $this->applyArticleSort($query, $sort);

        $articles = $query
            ->paginate(10)
            ->withQueryString();

        return view('admin.awareness.articles.index', [
            'articles' => $articles,
            'categories' => AwarenessCategory::query()->orderBy('name')->get(),
            'statuses' => AwarenessArticle::statuses(),
            'sortOptions' => $this->articleSortOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.awareness.articles.create', [
            'article' => new AwarenessArticle([
                'status' => AwarenessArticle::STATUS_DRAFT,
            ]),
            'categories' => AwarenessCategory::query()->active()->orderBy('name')->get(),
            'statuses' => AwarenessArticle::statuses(),
        ]);
    }

    public function store(StoreAwarenessArticleRequest $request): RedirectResponse
    {
        $data = $this->preparedData($request);
        $data['author_id'] = $request->user()->id;

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('awareness', 'public');
        }

        $article = AwarenessArticle::create($data);

        app(AuditLogger::class)->record(
            $request,
            AuditLog::MODULE_AWARENESS,
            'article_created',
            'Awareness article created.',
            $article,
            ['status' => $article->status]
        );

        return redirect()
            ->route('admin.awareness.articles.edit', $article)
            ->with('status', 'Awareness article created.');
    }

    public function show(AwarenessArticle $awarenessArticle): View
    {
        return view('admin.awareness.articles.show', [
            'article' => $awarenessArticle->load(['category', 'author']),
        ]);
    }

    public function edit(AwarenessArticle $awarenessArticle): View
    {
        return view('admin.awareness.articles.edit', [
            'article' => $awarenessArticle,
            'categories' => AwarenessCategory::query()->orderBy('name')->get(),
            'statuses' => AwarenessArticle::statuses(),
        ]);
    }

    public function update(UpdateAwarenessArticleRequest $request, AwarenessArticle $awarenessArticle): RedirectResponse
    {
        $data = $this->preparedData($request);

        if ($request->boolean('remove_featured_image') && $awarenessArticle->featured_image) {
            Storage::disk('public')->delete($awarenessArticle->featured_image);
            $data['featured_image'] = null;
        }

        if ($request->hasFile('featured_image')) {
            if ($awarenessArticle->featured_image) {
                Storage::disk('public')->delete($awarenessArticle->featured_image);
            }

            $data['featured_image'] = $request->file('featured_image')->store('awareness', 'public');
        }

        $previousStatus = $awarenessArticle->status;

        $awarenessArticle->update($data);

        app(AuditLogger::class)->record(
            $request,
            AuditLog::MODULE_AWARENESS,
            'article_updated',
            'Awareness article updated.',
            $awarenessArticle,
            ['previous_status' => $previousStatus, 'new_status' => $awarenessArticle->status]
        );

        return redirect()
            ->route('admin.awareness.articles.edit', $awarenessArticle)
            ->with('status', 'Awareness article updated.');
    }

    public function destroy(AwarenessArticle $awarenessArticle): RedirectResponse
    {
        $previousStatus = $awarenessArticle->status;

        $awarenessArticle->update([
            'status' => AwarenessArticle::STATUS_ARCHIVED,
        ]);

        app(AuditLogger::class)->record(
            request(),
            AuditLog::MODULE_AWARENESS,
            'article_archived',
            'Awareness article archived.',
            $awarenessArticle,
            ['previous_status' => $previousStatus, 'new_status' => AwarenessArticle::STATUS_ARCHIVED]
        );

        return redirect()
            ->route('admin.awareness.articles.index')
            ->with('status', 'Awareness article archived.');
    }

    public function publish(AwarenessArticle $awarenessArticle): RedirectResponse
    {
        $previousStatus = $awarenessArticle->status;

        $awarenessArticle->update([
            'status' => AwarenessArticle::STATUS_PUBLISHED,
            'published_at' => $awarenessArticle->published_at ?? now(),
        ]);

        app(AuditLogger::class)->record(
            request(),
            AuditLog::MODULE_AWARENESS,
            'article_published',
            'Awareness article published.',
            $awarenessArticle,
            ['previous_status' => $previousStatus, 'new_status' => AwarenessArticle::STATUS_PUBLISHED]
        );

        return back()->with('status', 'Awareness article published.');
    }

    public function archive(AwarenessArticle $awarenessArticle): RedirectResponse
    {
        $previousStatus = $awarenessArticle->status;

        $awarenessArticle->update([
            'status' => AwarenessArticle::STATUS_ARCHIVED,
        ]);

        app(AuditLogger::class)->record(
            request(),
            AuditLog::MODULE_AWARENESS,
            'article_archived',
            'Awareness article archived.',
            $awarenessArticle,
            ['previous_status' => $previousStatus, 'new_status' => AwarenessArticle::STATUS_ARCHIVED]
        );

        return back()->with('status', 'Awareness article archived.');
    }

    /**
     * @return array<string, mixed>
     */
    private function preparedData(StoreAwarenessArticleRequest|UpdateAwarenessArticleRequest $request): array
    {
        $data = $request->safe()->except(['featured_image', 'remove_featured_image']);

        if ($data['status'] === AwarenessArticle::STATUS_PUBLISHED && blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        if ($data['status'] !== AwarenessArticle::STATUS_PUBLISHED) {
            $data['published_at'] = null;
        }

        return $data;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<AwarenessArticle>  $query
     */
    private function applyArticleSort($query, string $sort): void
    {
        match ($sort) {
            'title' => $query->orderBy('title'),
            'published_newest' => $query->orderByDesc('published_at')->orderByDesc('updated_at'),
            'published_oldest' => $query->orderBy('published_at')->orderBy('updated_at'),
            'created_newest' => $query->orderByDesc('created_at')->orderByDesc('id'),
            'status' => $query->orderBy('status')->orderBy('title'),
            'category' => $query->orderBy('awareness_category_id')->orderBy('title'),
            default => $query->orderByDesc('updated_at')->orderByDesc('id'),
        };
    }

    /**
     * @return array<string, string>
     */
    private function articleSortOptions(): array
    {
        return [
            'updated' => 'Recently updated',
            'title' => 'Title',
            'published_newest' => 'Newest published',
            'published_oldest' => 'Oldest published',
            'created_newest' => 'Newest created',
            'status' => 'Status',
            'category' => 'Category',
        ];
    }

    /**
     * @param  array<string, string>  $options
     */
    private function normalizedSort(string $sort, array $options): string
    {
        return array_key_exists($sort, $options) ? $sort : 'updated';
    }
}
