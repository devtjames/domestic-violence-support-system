<?php

namespace Database\Seeders;

use App\Models\AbuseType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AbuseTypeSeeder extends Seeder
{
    public function run(): void
    {
        $abuseTypes = [
            ['Physical Abuse', 'Physical harm, threats of harm, restraint, or intimidation involving the body.'],
            ['Emotional Abuse', 'Humiliation, blame, manipulation, isolation, or repeated behavior that damages wellbeing.'],
            ['Psychological Abuse', 'Threats, coercion, intimidation, monitoring, and patterns that create fear or dependency.'],
            ['Sexual Abuse', 'Unwanted sexual contact, pressure, coercion, or exploitation.'],
            ['Financial Abuse', 'Control of money, work, documents, debt, or access to basic needs.'],
            ['Digital Abuse', 'Monitoring devices, passwords, messages, accounts, location, or online activity.'],
            ['Verbal Abuse', 'Threatening, degrading, frightening, or controlling language.'],
            ['Coercive Control', 'A repeated pattern of restrictions, surveillance, threats, and punishment.'],
            ['Child-related Abuse', 'Using children, parenting, or family arrangements to threaten or control someone.'],
            ['Other', 'Another concern that does not fit the listed categories.'],
        ];

        foreach ($abuseTypes as $index => [$name, $description]) {
            AbuseType::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $description,
                    'status' => AbuseType::STATUS_ACTIVE,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
