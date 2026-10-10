<?php

namespace Tests\Feature;

use App\Models\AnonymousCaseAccess;
use App\Models\SupportCase;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\CaseNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_update_cannot_mass_assign_sensitive_account_fields(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'account_status' => User::STATUS_ACTIVE,
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Updated Safe Name',
                'email' => $user->email,
                'role' => User::ROLE_ADMIN,
                'account_status' => User::STATUS_DISABLED,
                'password' => 'InjectedPassword123!',
                'email_verified_at' => now()->addYear()->toDateTimeString(),
            ])
            ->assertRedirect(route('profile.edit', absolute: false));

        $user->refresh();

        $this->assertSame('Updated Safe Name', $user->name);
        $this->assertSame(User::ROLE_USER, $user->role);
        $this->assertSame(User::STATUS_ACTIVE, $user->account_status);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_notification_open_redirects_only_to_internal_urls(): void
    {
        $user = User::factory()->create();

        $user->notify(new CaseNotification([
            'category' => 'security_review',
            'title' => 'Neutral notification',
            'body' => 'Open the platform for details.',
            'case_reference' => null,
            'action_url' => 'https://example.net/phishing',
        ]));

        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user)
            ->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_anonymous_tracking_accepts_configured_case_reference_prefix(): void
    {
        SystemSetting::query()->create([
            'setting_key' => SystemSetting::KEY_CASE_REFERENCE_PREFIX,
            'value' => 'SAFE',
        ]);

        $case = SupportCase::factory()->anonymous()->create();
        $case->anonymousAccess()->create([
            'access_code_hash' => AnonymousCaseAccess::hashAccessCode('SAFE1234'),
        ]);

        $this->assertStringStartsWith('SAFE-', $case->case_reference);

        $this->post(route('anonymous.track.lookup'), [
            'case_reference' => strtolower($case->case_reference),
            'access_code' => strtolower('SAFE1234'),
        ])
            ->assertOk()
            ->assertSee($case->case_reference);
    }

    public function test_sensitive_public_write_routes_are_throttled_and_sessions_are_hardened(): void
    {
        $registerRoute = collect(Route::getRoutes())
            ->first(fn ($route): bool => $route->uri() === 'register' && in_array('POST', $route->methods(), true));

        $this->assertNotNull($registerRoute);
        $this->assertContains('throttle:10,1', Route::getRoutes()->getByName('public.contact.store')->gatherMiddleware());
        $this->assertContains('throttle:10,1', Route::getRoutes()->getByName('anonymous.store')->gatherMiddleware());
        $this->assertContains('throttle:6,1', $registerRoute->gatherMiddleware());
        $this->assertContains('throttle:6,1', Route::getRoutes()->getByName('password.email')->gatherMiddleware());
        $this->assertContains('throttle:6,1', Route::getRoutes()->getByName('password.store')->gatherMiddleware());
        $this->assertTrue(config('session.encrypt'));
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('lax', config('session.same_site'));
    }
}
