<?php

namespace Tests\Feature\Admin;

use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_filter_and_view_registered_users_with_case_counts(): void
    {
        $admin = User::factory()->admin()->create();
        $activeUser = User::factory()->create([
            'name' => 'Central User',
            'email' => 'central@example.test',
            'account_status' => User::STATUS_ACTIVE,
        ]);
        $disabledUser = User::factory()->disabled()->create([
            'name' => 'Disabled User',
            'email' => 'disabled@example.test',
        ]);
        $officer = User::factory()->supportOfficer()->create([
            'name' => 'Central Officer',
            'email' => 'officer@example.test',
        ]);

        SupportCase::factory()->count(2)->create(['user_id' => $activeUser->id]);
        SupportCase::factory()->create(['user_id' => $disabledUser->id]);

        $response = $this->actingAs($admin)->get(route('admin.users.index', [
            'search' => 'Central',
            'account_status' => User::STATUS_ACTIVE,
        ]));

        $response->assertOk();
        $response->assertSee('Central User');
        $response->assertSee('central@example.test');
        $response->assertSee('2');
        $response->assertDontSee('Disabled User');
        $response->assertDontSee('Central Officer');
        $response->assertDontSee($activeUser->password);
    }

    public function test_admin_can_view_user_profile_and_case_account_information_without_passwords(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create([
            'name' => 'Profile User',
            'email' => 'profile@example.test',
        ]);
        $case = SupportCase::factory()->create([
            'user_id' => $user->id,
            'subject' => 'Sensitive user management subject marker',
            'description' => 'Sensitive user management description marker',
            'safe_contact_instruction' => 'Sensitive user management contact marker',
            'status' => SupportCase::STATUS_IN_PROGRESS,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.show', $user));

        $response->assertOk();
        $response->assertSee('Profile User');
        $response->assertSee('profile@example.test');
        $response->assertSee($case->case_reference);
        $response->assertSee('Submitted Cases');
        $response->assertDontSee('Sensitive user management subject marker');
        $response->assertDontSee('Sensitive user management description marker');
        $response->assertDontSee('Sensitive user management contact marker');
        $response->assertDontSee($user->password);
        $response->assertDontSee('name="password"', false);
        $response->assertDontSee('remember_token');
    }

    public function test_admin_can_activate_and_deactivate_registered_user_accounts(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create([
            'account_status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.deactivate', $user))
            ->assertSessionHas('status');

        $this->assertSame(User::STATUS_DISABLED, $user->refresh()->account_status);

        $this->actingAs($admin)
            ->patch(route('admin.users.activate', $user))
            ->assertSessionHas('status');

        $this->assertSame(User::STATUS_ACTIVE, $user->refresh()->account_status);
    }

    public function test_admin_user_management_only_manages_registered_users(): void
    {
        $admin = User::factory()->admin()->create();
        $officer = User::factory()->supportOfficer()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.show', $officer))
            ->assertNotFound();

        $this->actingAs($admin)
            ->patch(route('admin.users.deactivate', $officer))
            ->assertNotFound();

        $this->assertSame(User::STATUS_ACTIVE, $officer->refresh()->account_status);
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.users.show', $otherUser))
            ->assertForbidden();
    }
}
