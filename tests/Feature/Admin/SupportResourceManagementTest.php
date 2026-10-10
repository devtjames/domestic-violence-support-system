<?php

namespace Tests\Feature\Admin;

use App\Models\SupportResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportResourceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_support_resource(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.resources.store'), $this->validData([
            'organization_name' => 'Demo Legal Help Office',
            'resource_type' => SupportResource::TYPE_LEGAL_SUPPORT,
        ]));

        $resource = SupportResource::query()->where('organization_name', 'Demo Legal Help Office')->firstOrFail();

        $response->assertRedirect(route('admin.resources.edit', $resource, false));
        $this->assertDatabaseHas('support_resources', [
            'organization_name' => 'Demo Legal Help Office',
            'resource_type' => SupportResource::TYPE_LEGAL_SUPPORT,
            'status' => SupportResource::STATUS_ACTIVE,
        ]);
    }

    public function test_non_admin_cannot_access_support_resource_management(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.resources.index'))
            ->assertForbidden();
    }

    public function test_admin_can_update_and_delete_support_resource(): void
    {
        $admin = User::factory()->admin()->create();
        $resource = SupportResource::factory()->create([
            'organization_name' => 'Demo Community Office',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.resources.update', $resource), $this->validData([
                'organization_name' => 'Updated Demo Community Office',
                'status' => SupportResource::STATUS_INACTIVE,
            ]))
            ->assertRedirect(route('admin.resources.edit', $resource, false));

        $this->assertDatabaseHas('support_resources', [
            'id' => $resource->id,
            'organization_name' => 'Updated Demo Community Office',
            'status' => SupportResource::STATUS_INACTIVE,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.resources.destroy', $resource))
            ->assertRedirect(route('admin.resources.index', absolute: false));

        $this->assertSoftDeleted('support_resources', [
            'id' => $resource->id,
        ]);
    }

    public function test_support_resource_index_can_search_and_filter(): void
    {
        $admin = User::factory()->admin()->create();

        SupportResource::factory()->create([
            'organization_name' => 'Central Counselling Hub',
            'resource_type' => SupportResource::TYPE_COUNSELLING,
            'location' => 'Central District',
            'status' => SupportResource::STATUS_ACTIVE,
        ]);

        SupportResource::factory()->inactive()->create([
            'organization_name' => 'North Legal Office',
            'resource_type' => SupportResource::TYPE_LEGAL_SUPPORT,
            'location' => 'North Zone',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.resources.index', [
            'search' => 'Central',
            'resource_type' => SupportResource::TYPE_COUNSELLING,
            'location' => 'District',
            'status' => SupportResource::STATUS_ACTIVE,
        ]));

        $response->assertOk();
        $response->assertSee('Central Counselling Hub');
        $response->assertDontSee('North Legal Office');
    }

    public function test_public_resources_only_show_active_resources(): void
    {
        $active = SupportResource::factory()->create([
            'organization_name' => 'Visible Demo Resource',
            'status' => SupportResource::STATUS_ACTIVE,
        ]);

        $inactive = SupportResource::factory()->inactive()->create([
            'organization_name' => 'Hidden Demo Resource',
        ]);

        $this->get(route('public.resources'))
            ->assertOk()
            ->assertSee($active->organization_name)
            ->assertDontSee($inactive->organization_name);
    }

    public function test_support_resource_validation_checks_contact_fields(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.resources.store'), [
            'organization_name' => '',
            'resource_type' => 'Unknown Type',
            'description' => 'Too short',
            'phone' => 'call me',
            'email' => 'not-email',
            'website' => 'not-url',
            'location' => '',
            'availability' => '',
            'status' => 'published',
        ]);

        $response->assertSessionHasErrors([
            'organization_name',
            'resource_type',
            'description',
            'phone',
            'email',
            'website',
            'location',
            'availability',
            'status',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'organization_name' => 'Demo Support Resource',
            'resource_type' => SupportResource::TYPE_COUNSELLING,
            'description' => 'A verified demonstration resource description for support, referrals, and practical information.',
            'phone' => '555-0101',
            'email' => 'resource@example.test',
            'website' => 'https://example.test/resource',
            'address' => '10 Example Street',
            'location' => 'Central District',
            'availability' => 'Weekdays by appointment',
            'status' => SupportResource::STATUS_ACTIVE,
        ], $overrides);
    }
}
