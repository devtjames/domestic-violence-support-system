<?php

namespace Tests\Feature\Admin;

use App\Models\AbuseType;
use App\Models\AuditLog;
use App\Models\SupportCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbuseTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_activate_deactivate_and_delete_unused_abuse_type(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.abuse-types.store'), [
                'name' => 'Technology Monitoring',
                'slug' => '',
                'description' => 'Monitoring devices, accounts, passwords, or location.',
                'status' => AbuseType::STATUS_ACTIVE,
                'sort_order' => 12,
            ])
            ->assertRedirect();

        $abuseType = AbuseType::query()->where('slug', 'technology-monitoring')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'module' => AuditLog::MODULE_ABUSE_TYPES,
            'action' => 'abuse_type_created',
            'auditable_id' => $abuseType->id,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.abuse-types.update', $abuseType), [
                'name' => 'Digital Monitoring',
                'slug' => 'digital-monitoring',
                'description' => 'Updated educational concern category.',
                'status' => AbuseType::STATUS_INACTIVE,
                'sort_order' => 13,
            ])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('abuse_types', [
            'id' => $abuseType->id,
            'name' => 'Digital Monitoring',
            'slug' => 'digital-monitoring',
            'status' => AbuseType::STATUS_INACTIVE,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.abuse-types.activate', $abuseType))
            ->assertSessionHas('status');
        $this->assertSame(AbuseType::STATUS_ACTIVE, $abuseType->refresh()->status);

        $this->actingAs($admin)
            ->patch(route('admin.abuse-types.deactivate', $abuseType))
            ->assertSessionHas('status');
        $this->assertSame(AbuseType::STATUS_INACTIVE, $abuseType->refresh()->status);

        $this->actingAs($admin)
            ->delete(route('admin.abuse-types.destroy', $abuseType))
            ->assertRedirect(route('admin.abuse-types.index', absolute: false));

        $this->assertSoftDeleted('abuse_types', ['id' => $abuseType->id]);
    }

    public function test_abuse_type_index_can_search_and_filter(): void
    {
        $admin = User::factory()->admin()->create();

        AbuseType::factory()->create([
            'name' => 'Visible Digital Concern',
            'status' => AbuseType::STATUS_ACTIVE,
        ]);
        AbuseType::factory()->inactive()->create([
            'name' => 'Hidden Historical Concern',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.abuse-types.index', [
            'search' => 'Digital',
            'status' => AbuseType::STATUS_ACTIVE,
        ]));

        $response->assertOk();
        $response->assertSee('Visible Digital Concern');
        $response->assertDontSee('Hidden Historical Concern');
    }

    public function test_admin_cannot_delete_abuse_type_used_by_cases(): void
    {
        $admin = User::factory()->admin()->create();
        $abuseType = AbuseType::factory()->create();
        SupportCase::factory()->create(['abuse_type_id' => $abuseType->id]);

        $this->actingAs($admin)
            ->delete(route('admin.abuse-types.destroy', $abuseType))
            ->assertSessionHasErrors('abuse_type');

        $this->assertNotSoftDeleted('abuse_types', ['id' => $abuseType->id]);
    }

    public function test_only_active_abuse_types_are_available_on_intake_forms(): void
    {
        $user = User::factory()->create();
        $active = AbuseType::factory()->create(['name' => 'Active Intake Type']);
        $inactive = AbuseType::factory()->inactive()->create(['name' => 'Inactive Intake Type']);

        $this->actingAs($user)
            ->get(route('cases.create'))
            ->assertOk()
            ->assertSee($active->name)
            ->assertDontSee($inactive->name);

        $this->get(route('anonymous.create'))
            ->assertOk()
            ->assertSee($active->name)
            ->assertDontSee($inactive->name);
    }

    public function test_non_admin_cannot_access_abuse_type_management(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.abuse-types.index'))
            ->assertForbidden();
    }

    public function test_abuse_type_validation_requires_valid_fields(): void
    {
        $admin = User::factory()->admin()->create();
        $existing = AbuseType::factory()->create([
            'name' => 'Duplicate Type',
            'slug' => 'duplicate-type',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.abuse-types.store'), [
                'name' => $existing->name,
                'slug' => $existing->slug,
                'description' => str_repeat('A', 1001),
                'status' => 'published',
                'sort_order' => -1,
            ])
            ->assertSessionHasErrors(['name', 'slug', 'description', 'status', 'sort_order']);
    }
}
