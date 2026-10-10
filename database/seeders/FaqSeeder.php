<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            'Platform Privacy' => [
                ['Can Quick Exit erase my browser history?', 'No. Quick Exit only leaves this website quickly. Browser history, network logs, and device activity may still remain.'],
                ['Will my support case appear on public pages?', 'No. Support case information is protected by authentication and authorization rules.'],
            ],
            'Support Requests' => [
                ['Can I submit a support request after registering?', 'Yes. Registered users can submit a private support request and track updates from their dashboard.'],
                ['Is this platform an emergency service?', 'No. This academic platform does not contact emergency services, law enforcement, medical professionals, lawyers, or counsellors.'],
            ],
            'Anonymous Support' => [
                ['Can I ask for support without an account?', 'Yes. Anonymous support requests can be submitted without creating an account. Save the generated case reference and access code.'],
                ['Are anonymous case access codes stored as plain text?', 'No. Access codes are stored securely and checked through the anonymous tracking process.'],
            ],
            'Account Access' => [
                ['Can disabled accounts log in?', 'No. Disabled accounts are blocked from accessing protected areas.'],
                ['Can I update my profile and password?', 'Yes. Registered users, support officers, and administrators can manage their own profile and password.'],
            ],
        ];

        foreach ($items as $categoryName => $faqs) {
            $category = FaqCategory::query()->where('name', $categoryName)->first();

            if (! $category) {
                continue;
            }

            foreach ($faqs as $index => [$question, $answer]) {
                Faq::updateOrCreate(
                    ['question' => $question],
                    [
                        'faq_category_id' => $category->id,
                        'answer' => $answer,
                        'status' => Faq::STATUS_ACTIVE,
                        'sort_order' => $index + 1,
                    ]
                );
            }
        }
    }
}
