<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (SystemSetting::defaults() as $key => $value) {
            SystemSetting::query()->firstOrCreate(
                ['setting_key' => $key],
                ['value' => $value]
            );
        }
    }
}
