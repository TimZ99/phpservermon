<?php

namespace Database\Seeders;

use App\Settings\EmailSettings;
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
        EmailSettings::fake([
            'from_name' => 'PSM4',
            'from_address' => 'noreply@example.com',
        ]);
        NotificationSettings::fake([
            'telegram_bot_token' => env('TELEGRAM_BOT_TOKEN'),
        ]);
    }
}
