<?php

namespace Database\Seeders;

use App\Models\FaqCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FaqCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Platform Privacy', 'description' => 'Questions about privacy, browser history, and account safety.'],
            ['name' => 'Support Requests', 'description' => 'Questions about submitting and tracking support requests.'],
            ['name' => 'Anonymous Support', 'description' => 'Questions about anonymous case submission and tracking.'],
            ['name' => 'Account Access', 'description' => 'Questions about registration, login, and profile management.'],
        ];

        foreach ($categories as $index => $category) {
            FaqCategory::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'status' => FaqCategory::STATUS_ACTIVE,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
