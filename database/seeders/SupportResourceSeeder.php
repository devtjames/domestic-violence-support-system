<?php

namespace Database\Seeders;

use App\Models\SupportResource;
use Illuminate\Database\Seeder;

class SupportResourceSeeder extends Seeder
{
    public function run(): void
    {
        $resources = [
            [
                'organization_name' => 'Demo Community Counselling Centre',
                'resource_type' => SupportResource::TYPE_COUNSELLING,
                'description' => 'A fictional demonstration counselling resource for private appointments, emotional support, and referral guidance.',
                'phone' => null,
                'email' => 'counselling@example.test',
                'website' => 'https://example.test/counselling',
                'address' => '12 Example Street',
                'location' => 'Central District',
                'availability' => 'Weekdays by appointment',
                'status' => SupportResource::STATUS_ACTIVE,
            ],
            [
                'organization_name' => 'Demo Safe Accommodation Network',
                'resource_type' => SupportResource::TYPE_SHELTER,
                'description' => 'A fictional demonstration shelter coordination resource. Availability should be confirmed by an administrator before public reliance.',
                'phone' => null,
                'email' => 'shelter@example.test',
                'website' => null,
                'address' => null,
                'location' => 'North Zone',
                'availability' => 'Availability changes daily',
                'status' => SupportResource::STATUS_ACTIVE,
            ],
            [
                'organization_name' => 'Demo Legal Information Clinic',
                'resource_type' => SupportResource::TYPE_LEGAL_SUPPORT,
                'description' => 'A fictional demonstration resource for legal information, documentation guidance, and referrals to qualified professionals.',
                'phone' => null,
                'email' => 'legal@example.test',
                'website' => 'https://example.test/legal',
                'address' => '40 Sample Avenue',
                'location' => 'East District',
                'availability' => 'Tuesdays and Thursdays',
                'status' => SupportResource::STATUS_ACTIVE,
            ],
            [
                'organization_name' => 'Demo Family Health Referral Desk',
                'resource_type' => SupportResource::TYPE_MEDICAL_SUPPORT,
                'description' => 'A fictional demonstration resource for health service referrals and general medical support information.',
                'phone' => null,
                'email' => 'health@example.test',
                'website' => null,
                'address' => '7 Sample Road',
                'location' => 'West District',
                'availability' => 'Weekdays during office hours',
                'status' => SupportResource::STATUS_ACTIVE,
            ],
            [
                'organization_name' => 'Demo Child and Family Support Office',
                'resource_type' => SupportResource::TYPE_CHILD_PROTECTION,
                'description' => 'A fictional demonstration child and family support resource for safety planning and appropriate service referrals.',
                'phone' => null,
                'email' => 'family@example.test',
                'website' => 'https://example.test/family-support',
                'address' => null,
                'location' => 'South Zone',
                'availability' => 'Appointment based',
                'status' => SupportResource::STATUS_ACTIVE,
            ],
            [
                'organization_name' => 'Demo Urgent Safety Referral Desk',
                'resource_type' => SupportResource::TYPE_EMERGENCY_SUPPORT,
                'description' => 'A fictional demonstration placeholder for configurable emergency support information. Administrators must verify contacts before adding phone numbers.',
                'phone' => null,
                'email' => null,
                'website' => null,
                'address' => null,
                'location' => 'Configurable',
                'availability' => 'Admin verification required',
                'status' => SupportResource::STATUS_INACTIVE,
            ],
        ];

        foreach ($resources as $resource) {
            SupportResource::updateOrCreate(
                ['organization_name' => $resource['organization_name']],
                $resource
            );
        }
    }
}
