<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.default_locale', 'en');
        $this->migrator->add('general.timezone', 'CET');
        $this->migrator->add('notification.telegram_bot_token', '');
        $this->migrator->add('mail.chat_id', '');
        $this->migrator->add('mail.from_address', '');
        $this->migrator->add('mail.from_name', '');
    }
};
