<?php

namespace Tests\Feature\Admin;

use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_category_and_faq(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.faq-categories.store'), [
                'name' => 'Privacy Questions',
                'slug' => '',
                'description' => 'Questions about privacy and safe browsing.',
                'status' => FaqCategory::STATUS_ACTIVE,
                'sort_order' => 1,
            ])
            ->assertSessionHas('status');

        $category = FaqCategory::query()->where('slug', 'privacy-questions')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.faqs.store'), $this->validFaqData([
                'faq_category_id' => $category->id,
                'question' => 'Does Quick Exit delete browser history?',
                'answer' => 'No. It only leaves the website quickly. Browser history and device activity may still remain.',
            ]))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('faqs', [
            'faq_category_id' => $category->id,
            'question' => 'Does Quick Exit delete browser history?',
            'status' => Faq::STATUS_ACTIVE,
        ]);
    }

    public function test_admin_can_update_activate_deactivate_reorder_and_delete_faq(): void
    {
        $admin = User::factory()->admin()->create();
        $category = FaqCategory::factory()->create();
        $firstFaq = Faq::factory()->create([
            'faq_category_id' => $category->id,
            'question' => 'First ordered question?',
            'sort_order' => 1,
            'status' => Faq::STATUS_ACTIVE,
        ]);
        $secondFaq = Faq::factory()->create([
            'faq_category_id' => $category->id,
            'question' => 'Second ordered question?',
            'sort_order' => 2,
            'status' => Faq::STATUS_INACTIVE,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.faqs.update', $firstFaq), $this->validFaqData([
                'faq_category_id' => $category->id,
                'question' => 'Updated ordered question?',
                'status' => Faq::STATUS_INACTIVE,
            ]))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('faqs', [
            'id' => $firstFaq->id,
            'question' => 'Updated ordered question?',
            'status' => Faq::STATUS_INACTIVE,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.faqs.activate', $firstFaq))
            ->assertSessionHas('status');
        $this->assertSame(Faq::STATUS_ACTIVE, $firstFaq->refresh()->status);

        $this->actingAs($admin)
            ->patch(route('admin.faqs.deactivate', $firstFaq))
            ->assertSessionHas('status');
        $this->assertSame(Faq::STATUS_INACTIVE, $firstFaq->refresh()->status);

        $this->actingAs($admin)
            ->patch(route('admin.faqs.move-up', $secondFaq))
            ->assertSessionHas('status');

        $this->assertSame(1, $secondFaq->refresh()->sort_order);
        $this->assertSame(2, $firstFaq->refresh()->sort_order);

        $this->actingAs($admin)
            ->delete(route('admin.faqs.destroy', $firstFaq))
            ->assertRedirect(route('admin.faqs.index', absolute: false));

        $this->assertSoftDeleted('faqs', ['id' => $firstFaq->id]);
    }

    public function test_admin_can_manage_category_status_order_and_guard_delete_when_faqs_exist(): void
    {
        $admin = User::factory()->admin()->create();
        $firstCategory = FaqCategory::factory()->create([
            'name' => 'First Category',
            'sort_order' => 1,
            'status' => FaqCategory::STATUS_ACTIVE,
        ]);
        $secondCategory = FaqCategory::factory()->create([
            'name' => 'Second Category',
            'sort_order' => 2,
            'status' => FaqCategory::STATUS_INACTIVE,
        ]);
        Faq::factory()->create(['faq_category_id' => $firstCategory->id]);

        $this->actingAs($admin)
            ->patch(route('admin.faq-categories.deactivate', $firstCategory))
            ->assertSessionHas('status');
        $this->assertSame(FaqCategory::STATUS_INACTIVE, $firstCategory->refresh()->status);

        $this->actingAs($admin)
            ->patch(route('admin.faq-categories.activate', $secondCategory))
            ->assertSessionHas('status');
        $this->assertSame(FaqCategory::STATUS_ACTIVE, $secondCategory->refresh()->status);

        $this->actingAs($admin)
            ->patch(route('admin.faq-categories.move-up', $secondCategory))
            ->assertSessionHas('status');

        $this->assertSame(1, $secondCategory->refresh()->sort_order);
        $this->assertSame(2, $firstCategory->refresh()->sort_order);

        $this->actingAs($admin)
            ->delete(route('admin.faq-categories.destroy', $firstCategory))
            ->assertSessionHasErrors('category');
        $this->assertNotSoftDeleted('faq_categories', ['id' => $firstCategory->id]);
    }

    public function test_admin_faq_index_can_search_and_filter(): void
    {
        $admin = User::factory()->admin()->create();
        $privacyCategory = FaqCategory::factory()->create(['name' => 'Privacy']);
        $accountCategory = FaqCategory::factory()->create(['name' => 'Account']);

        Faq::factory()->create([
            'faq_category_id' => $privacyCategory->id,
            'question' => 'Can Quick Exit erase my history?',
            'status' => Faq::STATUS_ACTIVE,
        ]);
        Faq::factory()->inactive()->create([
            'faq_category_id' => $accountCategory->id,
            'question' => 'How do I update my password?',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.faqs.index', [
            'search' => 'Quick Exit',
            'category' => $privacyCategory->id,
            'status' => Faq::STATUS_ACTIVE,
        ]));

        $response->assertOk();
        $response->assertSee('Can Quick Exit erase my history?');
        $response->assertDontSee('How do I update my password?');
    }

    public function test_public_faqs_only_show_active_faqs_in_active_categories(): void
    {
        $activeCategory = FaqCategory::factory()->create([
            'name' => 'Visible Category',
            'status' => FaqCategory::STATUS_ACTIVE,
        ]);
        $inactiveCategory = FaqCategory::factory()->inactive()->create([
            'name' => 'Hidden Category',
        ]);

        $activeFaq = Faq::factory()->create([
            'faq_category_id' => $activeCategory->id,
            'question' => 'Visible public FAQ?',
            'answer' => 'This active answer should be visible publicly.',
            'status' => Faq::STATUS_ACTIVE,
        ]);
        $inactiveFaq = Faq::factory()->inactive()->create([
            'faq_category_id' => $activeCategory->id,
            'question' => 'Hidden inactive FAQ?',
        ]);
        $inactiveCategoryFaq = Faq::factory()->create([
            'faq_category_id' => $inactiveCategory->id,
            'question' => 'Hidden inactive category FAQ?',
        ]);

        $this->get(route('public.faqs'))
            ->assertOk()
            ->assertSee($activeCategory->name)
            ->assertSee($activeFaq->question)
            ->assertSee($activeFaq->answer)
            ->assertDontSee($inactiveFaq->question)
            ->assertDontSee($inactiveCategory->name)
            ->assertDontSee($inactiveCategoryFaq->question);
    }

    public function test_non_admin_cannot_access_faq_management(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.faqs.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.faq-categories.index'))
            ->assertForbidden();
    }

    public function test_faq_validation_requires_category_question_answer_status_and_order(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.faqs.store'), [
                'faq_category_id' => 999,
                'question' => '',
                'answer' => 'Too short',
                'status' => 'published',
                'sort_order' => -1,
            ])
            ->assertSessionHasErrors(['faq_category_id', 'question', 'answer', 'status', 'sort_order']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validFaqData(array $overrides = []): array
    {
        return array_merge([
            'faq_category_id' => FaqCategory::factory()->create()->id,
            'question' => 'Can I use this demonstration FAQ?',
            'answer' => 'This answer is long enough for validation and explains the demonstration FAQ clearly.',
            'status' => Faq::STATUS_ACTIVE,
            'sort_order' => 1,
        ], $overrides);
    }
}
