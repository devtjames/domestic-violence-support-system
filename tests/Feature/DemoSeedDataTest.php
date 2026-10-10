<?php

namespace Tests\Feature;

use App\Models\AbuseType;
use App\Models\AnonymousCaseAccess;
use App\Models\AwarenessArticle;
use App\Models\CaseActivity;
use App\Models\CaseMessage;
use App\Models\CaseNote;
use App\Models\Faq;
use App\Models\SupportCase;
use App\Models\SupportResource;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeedDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_fake_demo_accounts_and_content(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'role' => User::ROLE_ADMIN,
            'account_status' => User::STATUS_ACTIVE,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'officer@example.com',
            'role' => User::ROLE_SUPPORT_OFFICER,
            'staff_id' => 'SO-0001',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'disabled.user@example.test',
            'role' => User::ROLE_USER,
            'account_status' => User::STATUS_DISABLED,
        ]);

        $this->assertNotNull(User::where('email', 'user@example.com')->value('email_verified_at'));
        $this->assertGreaterThanOrEqual(3, User::where('role', User::ROLE_SUPPORT_OFFICER)->count());
        $this->assertGreaterThanOrEqual(10, User::where('role', User::ROLE_USER)->count());
        $this->assertGreaterThanOrEqual(10, AbuseType::count());
        $this->assertGreaterThanOrEqual(10, AwarenessArticle::count());
        $this->assertGreaterThanOrEqual(8, Faq::count());
        $this->assertGreaterThanOrEqual(6, SupportResource::count());
    }

    public function test_database_seeder_creates_demo_case_relationships_securely(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThanOrEqual(10, SupportCase::count());
        $this->assertGreaterThanOrEqual(3, SupportCase::where('is_anonymous', true)->count());
        $this->assertGreaterThanOrEqual(8, CaseMessage::count());
        $this->assertGreaterThanOrEqual(10, CaseActivity::count());
        $this->assertGreaterThanOrEqual(6, CaseNote::count());
        $this->assertGreaterThanOrEqual(3, AnonymousCaseAccess::count());

        $anonymousAccess = AnonymousCaseAccess::query()->firstOrFail();

        $this->assertNotSame('DEMOA123', $anonymousAccess->access_code_hash);
        $this->assertStringStartsWith('$2y$', $anonymousAccess->access_code_hash);
        $this->assertTrue(
            SupportCase::where('description', 'like', '%fictional%')
                ->orWhere('description', 'like', '%fake%')
                ->exists()
        );
    }

    public function test_database_seeder_is_repeatable(): void
    {
        $this->seed(DatabaseSeeder::class);

        $caseCount = SupportCase::count();
        $messageCount = CaseMessage::count();
        $activityCount = CaseActivity::count();
        $noteCount = CaseNote::count();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($caseCount, SupportCase::count());
        $this->assertSame($messageCount, CaseMessage::count());
        $this->assertSame($activityCount, CaseActivity::count());
        $this->assertSame($noteCount, CaseNote::count());
    }
}
