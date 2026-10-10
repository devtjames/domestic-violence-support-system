<?php

namespace Tests\Feature\Admin;

use App\Models\AwarenessArticle;
use App\Models\AwarenessCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AwarenessContentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.awareness.categories.store'), [
            'name' => 'Safety Planning',
            'slug' => '',
            'description' => 'Planning information for safer support decisions.',
            'status' => AwarenessCategory::STATUS_ACTIVE,
            'sort_order' => 10,
        ]);

        $response->assertRedirect(route('admin.awareness.categories.index', absolute: false));
        $this->assertDatabaseHas('awareness_categories', [
            'name' => 'Safety Planning',
            'slug' => 'safety-planning',
            'status' => AwarenessCategory::STATUS_ACTIVE,
        ]);
    }

    public function test_non_admin_cannot_access_awareness_management(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.awareness.articles.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_article_with_generated_slug_and_image(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $category = AwarenessCategory::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.awareness.articles.store'), [
            'title' => 'Digital Privacy Planning',
            'slug' => '',
            'awareness_category_id' => $category->id,
            'featured_image' => $this->fakePngUpload(),
            'short_description' => 'Practical information about privacy when using shared phones or online accounts.',
            'content' => 'This article explains careful privacy steps for people reading awareness information on shared devices or accounts.',
            'status' => AwarenessArticle::STATUS_DRAFT,
            'published_at' => '',
        ]);

        $article = AwarenessArticle::query()->where('slug', 'digital-privacy-planning')->firstOrFail();

        $response->assertRedirect(route('admin.awareness.articles.edit', $article, false));
        $this->assertSame($admin->id, $article->author_id);
        $this->assertNotNull($article->featured_image);
        Storage::disk('public')->assertExists($article->featured_image);
    }

    private function fakePngUpload(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'awareness-image');

        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='
        ));

        return new UploadedFile($path, 'privacy.png', 'image/png', null, true);
    }

    public function test_admin_can_publish_and_archive_article(): void
    {
        $admin = User::factory()->admin()->create();
        $article = AwarenessArticle::factory()->create([
            'author_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.awareness.articles.publish', $article))
            ->assertSessionHas('status');

        $article->refresh();
        $this->assertSame(AwarenessArticle::STATUS_PUBLISHED, $article->status);
        $this->assertNotNull($article->published_at);

        $this->actingAs($admin)
            ->patch(route('admin.awareness.articles.archive', $article))
            ->assertSessionHas('status');

        $this->assertSame(AwarenessArticle::STATUS_ARCHIVED, $article->refresh()->status);
    }

    public function test_article_index_can_search_and_filter(): void
    {
        $admin = User::factory()->admin()->create();
        $category = AwarenessCategory::factory()->create(['name' => 'Digital Abuse']);
        $otherCategory = AwarenessCategory::factory()->create(['name' => 'Safety Planning']);

        AwarenessArticle::factory()->published()->create([
            'awareness_category_id' => $category->id,
            'author_id' => $admin->id,
            'title' => 'Account Safety Basics',
        ]);

        AwarenessArticle::factory()->archived()->create([
            'awareness_category_id' => $otherCategory->id,
            'author_id' => $admin->id,
            'title' => 'Archived Planning Notes',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.awareness.articles.index', [
            'search' => 'Account',
            'category' => $category->id,
            'status' => AwarenessArticle::STATUS_PUBLISHED,
        ]));

        $response->assertOk();
        $response->assertSee('Account Safety Basics');
        $response->assertDontSee('Archived Planning Notes');
    }

    public function test_public_pages_only_show_published_articles(): void
    {
        $admin = User::factory()->admin()->create();
        $category = AwarenessCategory::factory()->create();

        $published = AwarenessArticle::factory()->published()->create([
            'awareness_category_id' => $category->id,
            'author_id' => $admin->id,
            'title' => 'Visible Awareness Article',
            'slug' => 'visible-awareness-article',
        ]);

        $draft = AwarenessArticle::factory()->create([
            'awareness_category_id' => $category->id,
            'author_id' => $admin->id,
            'title' => 'Hidden Draft Article',
            'slug' => 'hidden-draft-article',
        ]);

        $this->get(route('public.awareness'))
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee($draft->title);

        $this->get(route('public.awareness.show', $published->slug))->assertOk();
        $this->get(route('public.awareness.show', $draft->slug))->assertNotFound();
    }

    public function test_article_validation_requires_valid_category_and_content(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.awareness.articles.store'), [
            'title' => '',
            'awareness_category_id' => 999,
            'short_description' => 'Too short',
            'content' => 'Too short',
            'status' => 'invalid',
        ]);

        $response->assertSessionHasErrors([
            'title',
            'awareness_category_id',
            'short_description',
            'content',
            'status',
        ]);
    }
}
