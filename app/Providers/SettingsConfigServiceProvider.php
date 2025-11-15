<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class SettingsConfigServiceProvider extends ServiceProvider
{
    protected function canConnectToDatabase(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable $e) {
            \Sentry\captureException($e);
            logger()->info('Database not ready: '.$e->getMessage());

            return false;
        }
    }

    public function boot()
    {
        // check database connection, return if not.
        if (! $this->canConnectToDatabase()) {
            logger()->info('SettingsConfigServiceProvider: Database not ready, skipping config override. This could be expected behavior. For example: running an artisan command without the need for settings, like make.');

            return;
        }

        // check if settings table exists, return if not.
        if (! Schema::hasTable('settings')) {
            logger()->warning('SettingsConfigServiceProvider: Settings table doesn\'t exist. Run migrations.');

            return;
        }

        try {
            // get settings
            $generalSettings = app(\App\Settings\GeneralSettings::class);
            $notificationSettings = app(\App\Settings\NotificationSettings::class);

            // Set config
            config([
                'app.locale' => $generalSettings->default_locale ?? config('app.locale'),
                'app.timezone' => $generalSettings->timezone ?? config('app.timezone'),
                'email.from.name' => $notificationSettings->email_from_name ?? config('email.from.name'),
                'email.from.address' => $notificationSettings->email_from_address ?? config('email.from.address'),
                'mail.mailers.smtp.host' => $notificationSettings->email_host ?? config('mail.mailers.smtp.host'),
                'mail.mailers.smtp.port' => $notificationSettings->email_port ?? config('mail.mailers.smtp.port'),
                'mail.mailers.smtp.username' => $notificationSettings->email_username ?? config('mail.mailers.smtp.username'),
                'mail.mailers.smtp.password' => $notificationSettings->email_password ?? config('mail.mailers.smtp.password'),
                'mail.mailers.smtp.scheme' => $notificationSettings->email_encryption ?? config('mail.mailers.smtp.scheme'),
                'notification.telegram_bot_token' => $notificationSettings->telegram_bot_token ?? config('notification.telegram_bot_token'),
            ]);
        } catch (\Throwable $e) {
            \Sentry\captureException($e);
            // Fallback bij boot-time errors, zoals connection issues
            logger()->warning('Settings config override failed: '.$e->getMessage());
        }
    }
}
