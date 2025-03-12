<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('general', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('default_locale', 'en');
            $blueprint->add('timezone', 'Europe/Amsterdam');
        });
        $this->migrator->inGroup('mail', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('from_address', '');
            $blueprint->add('from_name', '');
        });
        $this->migrator->inGroup('notification', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('telegram_bot_token', '');
        });
    }

    public function down(): void
    {
        $this->migrator->inGroup('general', function (SettingsBlueprint $blueprint): void {
            $blueprint->delete('default_locale');
            $blueprint->delete('timezone');
        });
        $this->migrator->inGroup('mail', function (SettingsBlueprint $blueprint): void {
            $blueprint->delete('from_address');
            $blueprint->delete('from_name');
        });
        $this->migrator->inGroup('notification', function (SettingsBlueprint $blueprint): void {
            $blueprint->delete('telegram_bot_token');
        });
    }
};
