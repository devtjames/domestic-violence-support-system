<?php

namespace Database\Seeders;

use App\Models\AwarenessArticle;
use App\Models\AwarenessCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AwarenessArticleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()
            ->where('role', User::ROLE_ADMIN)
            ->first()
            ?? User::factory()->admin()->create([
                'name' => 'Demo Administrator',
                'email' => 'admin@example.com',
            ]);

        $articles = [
            ['Understanding Patterns of Control', 'Awareness', 'Domestic violence is often a pattern of control rather than a single incident.', AwarenessArticle::STATUS_PUBLISHED],
            ['Digital Safety Basics', 'Digital Abuse', 'Phones, shared accounts, and browser history can affect privacy when seeking help online.', AwarenessArticle::STATUS_PUBLISHED],
            ['Planning Safer Conversations', 'Safety Planning', 'A safer conversation plan can help reduce risk when reaching out for support.', AwarenessArticle::STATUS_PUBLISHED],
            ['Recognizing Emotional Abuse', 'Emotional Abuse', 'Emotional abuse can include humiliation, blame, intimidation, and isolation.', AwarenessArticle::STATUS_PUBLISHED],
            ['Understanding Financial Control', 'Financial Abuse', 'Financial control can limit independence, movement, communication, and choices.', AwarenessArticle::STATUS_PUBLISHED],
            ['Support After Leaving', 'Recovery and Support', 'Recovery support can include counselling, community care, practical planning, and time.', AwarenessArticle::STATUS_PUBLISHED],
            ['Warning Signs in Digital Accounts', 'Digital Abuse', 'Unexpected logins, shared passwords, and location tracking may affect safety.', AwarenessArticle::STATUS_PUBLISHED],
            ['Children and Safety Planning', 'Child-related Abuse', 'Safety planning may need to include school contacts, documents, and trusted adults.', AwarenessArticle::STATUS_PUBLISHED],
            ['When Abuse Is Not Physical', 'Psychological Abuse', 'Non-physical abuse can still create fear, restriction, and harm.', AwarenessArticle::STATUS_PUBLISHED],
            ['Preparing Important Documents', 'Safety Planning', 'Where safe, copies of documents and essential information can support planning.', AwarenessArticle::STATUS_PUBLISHED],
            ['How Support Requests Will Work', 'Safety Planning', 'Future support requests will be private and handled through protected workflows.', AwarenessArticle::STATUS_DRAFT],
            ['Archived Demonstration Article', 'Awareness', 'This archived demonstration article should not appear publicly.', AwarenessArticle::STATUS_ARCHIVED],
        ];

        foreach ($articles as $index => [$title, $categoryName, $summary, $status]) {
            $category = AwarenessCategory::query()->where('name', $categoryName)->first()
                ?? AwarenessCategory::query()->first();

            AwarenessArticle::updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'awareness_category_id' => $category->id,
                    'author_id' => $admin->id,
                    'title' => $title,
                    'featured_image' => null,
                    'short_description' => $summary,
                    'content' => $this->contentFor($title),
                    'status' => $status,
                    'published_at' => $status === AwarenessArticle::STATUS_PUBLISHED ? now()->subDays($index + 1) : null,
                ]
            );
        }
    }

    private function contentFor(string $title): string
    {
        return implode("\n\n", [
            "{$title} is provided as educational information for the awareness section of this academic support platform.",
            'Domestic violence may involve patterns of control, intimidation, isolation, threats, or harm. A person seeking help should be able to read information privately and choose a safer next step.',
            'This content avoids graphic detail and does not replace emergency, medical, legal, or counselling services. Where there is immediate danger, a verified local emergency or professional support service should be used.',
            'Future modules will connect this information with private support requests, case tracking, and carefully authorized staff workflows.',
        ]);
    }
}
