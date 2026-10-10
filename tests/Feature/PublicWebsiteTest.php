<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_pages_render_for_visitors(): void
    {
        $routes = [
            route('home', absolute: false),
            route('public.about', absolute: false),
            route('public.types', absolute: false),
            route('public.warning-signs', absolute: false),
            route('public.safety', absolute: false),
            route('public.awareness', absolute: false),
            route('public.resources', absolute: false),
            route('public.faqs', absolute: false),
            route('public.get-support', absolute: false),
            route('public.contact', absolute: false),
            route('login', absolute: false),
            route('register', absolute: false),
        ];

        foreach ($routes as $route) {
            $this->get($route)->assertOk();
        }
    }

    public function test_home_page_contains_privacy_aware_support_links(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Domestic Support');
        $response->assertSee('Get Support');
        $response->assertSee('Anonymous Help');
        $response->assertSee('Quick Exit');
        $response->assertSee('Quick Exit does not erase browser history');
    }

    public function test_awareness_article_detail_renders(): void
    {
        $this->get(route('public.awareness.show', 'digital-safety-basics'))
            ->assertOk()
            ->assertSee('Digital Safety Basics')
            ->assertSee('Quick Exit');
    }

    public function test_unknown_awareness_article_returns_not_found(): void
    {
        $this->get(route('public.awareness.show', 'missing-article'))
            ->assertNotFound();
    }

    public function test_quick_exit_uses_system_setting_destination(): void
    {
        SystemSetting::setMany([
            SystemSetting::KEY_QUICK_EXIT_URL => 'https://example.com',
        ]);

        $this->get(route('public.quick-exit'))
            ->assertRedirect('https://example.com');
    }
}
