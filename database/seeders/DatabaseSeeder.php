<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! App::environment(['local', 'testing'])) {
            return;
        }

        $this->updateDemoUser('admin@example.com', [
            'name' => 'Demo Administrator',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'account_status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->updateDemoUser('officer@example.com', [
            'name' => 'Demo Support Officer',
            'phone' => '555-0101',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPPORT_OFFICER,
            'account_status' => User::STATUS_ACTIVE,
            'staff_id' => 'SO-0001',
            'specialization' => 'General Support',
            'date_joined' => now()->subYear()->toDateString(),
            'email_verified_at' => now(),
        ]);

        $this->updateDemoUser('counselling.officer@example.com', [
            'name' => 'Demo Counselling Officer',
            'phone' => '555-0102',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPPORT_OFFICER,
            'account_status' => User::STATUS_ACTIVE,
            'staff_id' => 'SO-0002',
            'specialization' => 'Counselling',
            'date_joined' => now()->subMonths(10)->toDateString(),
            'email_verified_at' => now(),
        ]);

        $this->updateDemoUser('legal.officer@example.com', [
            'name' => 'Demo Legal Support Officer',
            'phone' => '555-0103',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPPORT_OFFICER,
            'account_status' => User::STATUS_ACTIVE,
            'staff_id' => 'SO-0003',
            'specialization' => 'Legal Support',
            'date_joined' => now()->subMonths(8)->toDateString(),
            'email_verified_at' => now(),
        ]);

        $this->updateDemoUser('user@example.com', [
            'name' => 'Demo Registered User',
            'password' => Hash::make('password'),
            'role' => User::ROLE_USER,
            'account_status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        foreach ($this->demoUsers() as $user) {
            $this->updateDemoUser($user['email'], [
                'name' => $user['name'],
                'phone' => $user['phone'],
                'gender' => $user['gender'],
                'password' => Hash::make('password'),
                'role' => User::ROLE_USER,
                'account_status' => $user['account_status'],
                'email_verified_at' => now(),
            ]);
        }

        $this->call([
            AbuseTypeSeeder::class,
            AwarenessCategorySeeder::class,
            AwarenessArticleSeeder::class,
            SupportResourceSeeder::class,
            FaqCategorySeeder::class,
            FaqSeeder::class,
            SystemSettingSeeder::class,
            DemoSupportCaseSeeder::class,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function updateDemoUser(string $email, array $attributes): void
    {
        $user = User::query()->firstOrNew(['email' => $email]);

        $user->fill([
            'name' => $attributes['name'],
            'email' => $email,
            'phone' => $attributes['phone'] ?? null,
            'gender' => $attributes['gender'] ?? null,
            'staff_id' => $attributes['staff_id'] ?? null,
            'specialization' => $attributes['specialization'] ?? null,
            'date_joined' => $attributes['date_joined'] ?? null,
        ]);

        $user->forceFill([
            'password' => $attributes['password'],
            'role' => $attributes['role'],
            'account_status' => $attributes['account_status'],
            'email_verified_at' => $attributes['email_verified_at'],
        ])->save();
    }

    /**
     * @return array<int, array{name: string, email: string, phone: string|null, gender: string, account_status: string}>
     */
    private function demoUsers(): array
    {
        return [
            ['name' => 'Amina Bello', 'email' => 'amina.bello@example.test', 'phone' => '555-0201', 'gender' => 'female', 'account_status' => User::STATUS_ACTIVE],
            ['name' => 'Grace Mensah', 'email' => 'grace.mensah@example.test', 'phone' => '555-0202', 'gender' => 'female', 'account_status' => User::STATUS_ACTIVE],
            ['name' => 'Nadia Cole', 'email' => 'nadia.cole@example.test', 'phone' => null, 'gender' => 'prefer_not_to_say', 'account_status' => User::STATUS_ACTIVE],
            ['name' => 'Maya Rivers', 'email' => 'maya.rivers@example.test', 'phone' => '555-0204', 'gender' => 'female', 'account_status' => User::STATUS_ACTIVE],
            ['name' => 'Sam Taylor', 'email' => 'sam.taylor@example.test', 'phone' => null, 'gender' => 'non_binary', 'account_status' => User::STATUS_ACTIVE],
            ['name' => 'Leah Brooks', 'email' => 'leah.brooks@example.test', 'phone' => '555-0206', 'gender' => 'female', 'account_status' => User::STATUS_ACTIVE],
            ['name' => 'Jonah Reed', 'email' => 'jonah.reed@example.test', 'phone' => null, 'gender' => 'male', 'account_status' => User::STATUS_ACTIVE],
            ['name' => 'Priya Shah', 'email' => 'priya.shah@example.test', 'phone' => '555-0208', 'gender' => 'female', 'account_status' => User::STATUS_ACTIVE],
            ['name' => 'Noah Clarke', 'email' => 'noah.clarke@example.test', 'phone' => null, 'gender' => 'male', 'account_status' => User::STATUS_ACTIVE],
            ['name' => 'Disabled Demo User', 'email' => 'disabled.user@example.test', 'phone' => null, 'gender' => 'prefer_not_to_say', 'account_status' => User::STATUS_DISABLED],
        ];
    }
}
