<?php

namespace Tests\Feature\Admin;

use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SupportOfficerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_filter_and_view_support_officers_with_assigned_case_counts(): void
    {
        $admin = User::factory()->admin()->create();
        $officer = User::factory()->supportOfficer()->create([
            'name' => 'Central Officer',
            'email' => 'central.officer@example.test',
            'staff_id' => 'SO-CENTRAL',
            'specialization' => 'Counselling',
            'account_status' => User::STATUS_ACTIVE,
        ]);
        $disabledOfficer = User::factory()->supportOfficer()->disabled()->create([
            'name' => 'Disabled Officer',
            'staff_id' => 'SO-DISABLED',
            'specialization' => 'Legal Support',
        ]);
        $registeredUser = User::factory()->create([
            'name' => 'Central Registered User',
            'email' => 'central.user@example.test',
        ]);

        SupportCase::factory()->count(2)->assignedTo($officer)->create();
        SupportCase::factory()->assignedTo($disabledOfficer)->create();

        $response = $this->actingAs($admin)->get(route('admin.support-officers.index', [
            'search' => 'Central',
            'account_status' => User::STATUS_ACTIVE,
            'specialization' => 'Counselling',
        ]));

        $response->assertOk();
        $response->assertSee('Central Officer');
        $response->assertSee('central.officer@example.test');
        $response->assertSee('SO-CENTRAL');
        $response->assertSee('Counselling');
        $response->assertSee('2');
        $response->assertDontSee('Disabled Officer');
        $response->assertDontSee('Central Registered User');
        $response->assertDontSee($registeredUser->email);
    }

    public function test_admin_can_create_support_officer_with_hashed_password_and_staff_details(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.support-officers.store'), [
            'name' => 'New Staff Officer',
            'email' => 'new.officer@example.test',
            'phone' => '+1 555 0104',
            'gender' => 'prefer_not_to_say',
            'staff_id' => 'SO-NEW-001',
            'specialization' => 'Child Protection',
            'account_status' => User::STATUS_ACTIVE,
            'date_joined' => now()->toDateString(),
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
        ]);

        $officer = User::query()->where('email', 'new.officer@example.test')->firstOrFail();

        $response->assertRedirect(route('admin.support-officers.show', $officer));
        $this->assertTrue($officer->isSupportOfficer());
        $this->assertSame('SO-NEW-001', $officer->staff_id);
        $this->assertSame('Child Protection', $officer->specialization);
        $this->assertTrue(Hash::check('StrongPassword123!', $officer->password));
        $this->assertNotSame('StrongPassword123!', $officer->password);
    }

    public function test_admin_can_view_and_update_officer_profile_without_exposing_password_fields(): void
    {
        $admin = User::factory()->admin()->create();
        $officer = User::factory()->supportOfficer()->create([
            'name' => 'Profile Officer',
            'email' => 'profile.officer@example.test',
            'staff_id' => 'SO-PROFILE',
            'specialization' => 'General Support',
        ]);
        $case = SupportCase::factory()->assignedTo($officer)->create([
            'subject' => 'Sensitive officer profile subject marker',
            'description' => 'Sensitive officer profile description marker',
            'safe_contact_instruction' => 'Sensitive officer profile contact marker',
        ]);

        $showResponse = $this->actingAs($admin)->get(route('admin.support-officers.show', $officer));

        $showResponse->assertOk();
        $showResponse->assertSee('Profile Officer');
        $showResponse->assertSee('SO-PROFILE');
        $showResponse->assertSee($case->case_reference);
        $showResponse->assertDontSee('Sensitive officer profile subject marker');
        $showResponse->assertDontSee('Sensitive officer profile description marker');
        $showResponse->assertDontSee('Sensitive officer profile contact marker');
        $showResponse->assertDontSee($officer->password);
        $showResponse->assertDontSee('name="password"', false);

        $editResponse = $this->actingAs($admin)->get(route('admin.support-officers.edit', $officer));
        $editResponse->assertOk();
        $editResponse->assertDontSee('name="password"', false);

        $this->actingAs($admin)->patch(route('admin.support-officers.update', $officer), [
            'name' => 'Updated Officer',
            'email' => 'updated.officer@example.test',
            'phone' => '555-0199',
            'gender' => 'female',
            'staff_id' => 'SO-UPDATED',
            'specialization' => 'Social Support',
            'account_status' => User::STATUS_ACTIVE,
            'date_joined' => now()->subDay()->toDateString(),
        ])->assertRedirect(route('admin.support-officers.show', $officer));

        $officer->refresh();
        $this->assertSame('Updated Officer', $officer->name);
        $this->assertSame('SO-UPDATED', $officer->staff_id);
        $this->assertSame('Social Support', $officer->specialization);
    }

    public function test_admin_can_activate_and_deactivate_officer_while_preserving_assigned_cases(): void
    {
        $admin = User::factory()->admin()->create();
        $officer = User::factory()->supportOfficer()->create([
            'account_status' => User::STATUS_ACTIVE,
        ]);
        $case = SupportCase::factory()->assignedTo($officer)->create();

        $this->actingAs($admin)
            ->patch(route('admin.support-officers.deactivate', $officer))
            ->assertSessionHas('status');

        $this->assertSame(User::STATUS_DISABLED, $officer->refresh()->account_status);
        $this->assertSame($officer->id, $case->refresh()->assigned_officer_id);

        $this->actingAs($admin)
            ->patch(route('admin.support-officers.activate', $officer))
            ->assertSessionHas('status');

        $this->assertSame(User::STATUS_ACTIVE, $officer->refresh()->account_status);
        $this->assertSame($officer->id, $case->refresh()->assigned_officer_id);
    }

    public function test_support_officer_management_only_manages_support_officer_accounts(): void
    {
        $admin = User::factory()->admin()->create();
        $registeredUser = User::factory()->create();
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.support-officers.show', $registeredUser))
            ->assertNotFound();

        $this->actingAs($admin)
            ->patch(route('admin.support-officers.deactivate', $otherAdmin))
            ->assertNotFound();

        $this->assertSame(User::STATUS_ACTIVE, $registeredUser->refresh()->account_status);
        $this->assertSame(User::STATUS_ACTIVE, $otherAdmin->refresh()->account_status);
    }

    public function test_officer_validation_rejects_invalid_staff_details(): void
    {
        $admin = User::factory()->admin()->create();
        $existingOfficer = User::factory()->supportOfficer()->create([
            'staff_id' => 'SO-EXISTS',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.support-officers.store'), [
            'name' => '',
            'email' => $existingOfficer->email,
            'phone' => 'call me',
            'gender' => 'unsupported',
            'staff_id' => 'SO-EXISTS',
            'specialization' => 'Unsupported',
            'account_status' => 'pending',
            'date_joined' => now()->addDay()->toDateString(),
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors([
            'name',
            'email',
            'phone',
            'gender',
            'staff_id',
            'specialization',
            'account_status',
            'date_joined',
            'password',
        ]);
    }

    public function test_non_admin_cannot_access_support_officer_management(): void
    {
        $user = User::factory()->create();
        $officer = User::factory()->supportOfficer()->create();

        $this->actingAs($user)
            ->get(route('admin.support-officers.index'))
            ->assertForbidden();

        $this->actingAs($officer)
            ->get(route('admin.support-officers.create'))
            ->assertForbidden();
    }
}
