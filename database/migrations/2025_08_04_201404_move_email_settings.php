<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->migrator->rename(
            'mail.from_address',
            'notification.email_from_address'
        );

        $this->migrator->rename(
            'mail.from_name',
            'notification.email_from_name'
        );

        $this->migrator->rename(
            'mail.email_notifications_enabled',
            'notification.email_global_enabled'
        );

        $this->migrator->rename(
            'notification.telegram_notifications_enabled',
            'notification.telegram_global_enabled'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->migrator->rename(
            'notification.email_from_address',
            'mail.from_address'
        );

        $this->migrator->rename(
            'notification.email_from_name',
            'mail.from_name'
        );

        $this->migrator->rename(
            'notification.email_global_enabled',
            'mail.email_notifications_enabled'
        );
        $this->migrator->rename(
            'notification.telegram_global_enabled',
            'notification.telegram_notifications_enabled'
        );
    }
};
