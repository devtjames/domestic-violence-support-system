<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_can_only_access_user_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->actingAs($user)->get(route('officer.dashboard'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_support_officer_can_only_access_officer_dashboard(): void
    {
        $officer = User::factory()->supportOfficer()->create();

        $this->actingAs($officer)->get(route('officer.dashboard'))->assertOk();
        $this->actingAs($officer)->get(route('dashboard'))->assertForbidden();
        $this->actingAs($officer)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_can_only_access_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('officer.dashboard'))->assertForbidden();
    }

    public function test_support_officer_login_redirects_to_officer_dashboard(): void
    {
        $officer = User::factory()->supportOfficer()->create();

        $response = $this->post(route('login'), [
            'email' => $officer->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('officer.dashboard', absolute: false));
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_disabled_account_cannot_login(): void
    {
        $user = User::factory()->disabled()->create();

        $response = $this->from(route('login'))->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionHasErrors('email');
    }

    public function test_disabled_authenticated_account_is_logged_out_from_protected_routes(): void
    {
        $user = User::factory()->disabled()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionHasErrors('email');
    }
}
