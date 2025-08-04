<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->migrator->inGroup('mail', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('email_notifications_enabled', false);
        });
        $this->migrator->inGroup('notification', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('telegram_notifications_enabled', false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->migrator->inGroup('mail', function (SettingsBlueprint $blueprint): void {
            $blueprint->delete('email_notifications_enabled');
        });
        $this->migrator->inGroup('notification', function (SettingsBlueprint $blueprint): void {
            $blueprint->delete('telegram_notifications_enabled');
        });
    }
};
