<?php

namespace Tests\Feature\Admin;

use App\Models\AbuseType;
use App\Models\AuditLog;
use App\Models\SupportCase;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_update_and_apply_system_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $payload = $this->validSettingsPayload([
            SystemSetting::KEY_PLATFORM_NAME => 'Community Support Portal',
            SystemSetting::KEY_PLATFORM_EMAIL => 'platform@example.test',
            SystemSetting::KEY_SUPPORT_EMAIL => 'support@example.test',
            SystemSetting::KEY_SUPPORT_PHONE => '+1 555 0200',
            SystemSetting::KEY_ADDRESS => "123 Support Street\nSuite 4",
            SystemSetting::KEY_QUICK_EXIT_URL => 'https://example.org/neutral',
            SystemSetting::KEY_CASE_REFERENCE_PREFIX => 'SAFE',
            SystemSetting::KEY_CONTACT_INFORMATION => 'Use this page for general platform questions only.',
            SystemSetting::KEY_CRITICAL_CASE_GUIDANCE => 'Use verified local emergency support now. This platform does not dispatch help.',
            SystemSetting::KEY_HOMEPAGE_EYEBROW => 'Private local support',
            SystemSetting::KEY_HOMEPAGE_HEADING => 'Community Support Portal',
            SystemSetting::KEY_HOMEPAGE_INTRO => 'A calm configurable homepage message for visitors.',
            SystemSetting::KEY_FOOTER_INFORMATION => 'Configurable footer information for the academic support platform.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('System Settings')
            ->assertSee('Quick Exit Destination');

        $this->actingAs($admin)
            ->patch(route('admin.settings.update'), $payload)
            ->assertRedirect(route('admin.settings.edit', absolute: false))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('system_settings', [
            'setting_key' => SystemSetting::KEY_PLATFORM_NAME,
            'value' => 'Community Support Portal',
        ]);
        $this->assertDatabaseHas('system_settings', [
            'setting_key' => SystemSetting::KEY_QUICK_EXIT_URL,
            'value' => 'https://example.org/neutral',
        ]);
        $this->assertDatabaseHas('system_settings', [
            'setting_key' => SystemSetting::KEY_CRITICAL_CASE_GUIDANCE,
            'value' => 'Use verified local emergency support now. This platform does not dispatch help.',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'module' => AuditLog::MODULE_SETTINGS,
            'action' => 'settings_updated',
            'description' => 'System settings updated.',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Community Support Portal')
            ->assertSee('A calm configurable homepage message for visitors.')
            ->assertSee('Configurable footer information for the academic support platform.');

        $this->get(route('public.contact'))
            ->assertOk()
            ->assertSee('support@example.test')
            ->assertSee('+1 555 0200')
            ->assertSee('123 Support Street');

        $this->get(route('public.quick-exit'))
            ->assertRedirect('https://example.org/neutral');

        AbuseType::factory()->create();

        $this->get(route('anonymous.create'))
            ->assertOk()
            ->assertSee('Use verified local emergency support now.');
    }

    public function test_only_admins_can_manage_system_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.settings.edit'))
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('admin.settings.update'), $this->validSettingsPayload())
            ->assertForbidden();
    }

    public function test_system_settings_validation_blocks_invalid_values(): void
    {
        $admin = User::factory()->admin()->create();

        $payload = $this->validSettingsPayload([
            SystemSetting::KEY_PLATFORM_NAME => '',
            SystemSetting::KEY_SUPPORT_PHONE => 'not a phone',
            SystemSetting::KEY_QUICK_EXIT_URL => 'javascript:alert(1)',
            SystemSetting::KEY_CASE_REFERENCE_PREFIX => 'unsafe prefix!',
            SystemSetting::KEY_CRITICAL_CASE_GUIDANCE => '',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.settings.update'), $payload)
            ->assertSessionHasErrors([
                SystemSetting::KEY_PLATFORM_NAME,
                SystemSetting::KEY_SUPPORT_PHONE,
                SystemSetting::KEY_QUICK_EXIT_URL,
                SystemSetting::KEY_CASE_REFERENCE_PREFIX,
                SystemSetting::KEY_CRITICAL_CASE_GUIDANCE,
            ]);
    }

    public function test_case_references_use_configured_prefix_and_remain_unique(): void
    {
        SystemSetting::setMany([
            SystemSetting::KEY_CASE_REFERENCE_PREFIX => 'SAFE',
        ]);

        $firstCase = SupportCase::factory()->create();
        $secondCase = SupportCase::factory()->create();

        $this->assertMatchesRegularExpression('/^SAFE-\d{4}-000001$/', $firstCase->case_reference);
        $this->assertMatchesRegularExpression('/^SAFE-\d{4}-000002$/', $secondCase->case_reference);
    }

    /**
     * @param  array<string, string|null>  $overrides
     * @return array<string, string|null>
     */
    private function validSettingsPayload(array $overrides = []): array
    {
        return array_merge(SystemSetting::defaults(), [
            SystemSetting::KEY_PLATFORM_NAME => 'Domestic Support',
            SystemSetting::KEY_PLATFORM_EMAIL => 'platform@example.test',
            SystemSetting::KEY_SUPPORT_EMAIL => 'support@example.test',
            SystemSetting::KEY_SUPPORT_PHONE => '+1 555 0100',
            SystemSetting::KEY_ADDRESS => 'Demo support office',
            SystemSetting::KEY_QUICK_EXIT_URL => 'https://www.google.com',
            SystemSetting::KEY_CASE_REFERENCE_PREFIX => 'DV',
            SystemSetting::KEY_CONTACT_INFORMATION => 'General enquiries only.',
            SystemSetting::KEY_CRITICAL_CASE_GUIDANCE => 'If there is immediate danger, use verified local emergency support. This academic platform does not dispatch emergency assistance.',
            SystemSetting::KEY_HOMEPAGE_EYEBROW => 'Private awareness and support',
            SystemSetting::KEY_HOMEPAGE_HEADING => 'Domestic Support',
            SystemSetting::KEY_HOMEPAGE_INTRO => 'Learn about domestic violence and request support privately.',
            SystemSetting::KEY_FOOTER_INFORMATION => 'Academic support platform information.',
        ], $overrides);
    }
}
