<?php

namespace Database\Seeders;

use App\Models\AwarenessCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AwarenessCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Physical Abuse', 'Information about physical harm, intimidation, restraint, and related safety concerns.'],
            ['Emotional Abuse', 'Information about humiliation, blame, manipulation, and emotional control.'],
            ['Psychological Abuse', 'Information about threats, fear, isolation, and coercive behavior.'],
            ['Financial Abuse', 'Information about money control, employment restrictions, debt, and access to essentials.'],
            ['Sexual Abuse', 'Information about consent, pressure, coercion, and sexual exploitation.'],
            ['Digital Abuse', 'Information about device privacy, account monitoring, tracking, and online safety.'],
            ['Child-related Abuse', 'Information about child safety, parenting pressure, and family-related control.'],
            ['Safety Planning', 'Practical education for safer preparation, support contacts, and privacy awareness.'],
            ['Recovery and Support', 'Information about ongoing support, wellbeing, confidence rebuilding, and referrals.'],
        ];

        foreach ($categories as $index => [$name, $description]) {
            AwarenessCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $description,
                    'status' => AwarenessCategory::STATUS_ACTIVE,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
