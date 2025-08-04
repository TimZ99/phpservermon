<?php

namespace Database\Seeders;

use App\Settings\GeneralSettings;
use App\Settings\NotificationSettings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        GeneralSettings::fake([
            'locale' => 'en',
            'timezone' => 'Europe/Amsterdam',
        ]);
        NotificationSettings::fake([
            'from_name' => 'PSM4',
            'from_address' => 'noreply@example.com',
            'telegram_bot_token' => env('TELEGRAM_BOT_TOKEN'),
        ]);
    }
}
