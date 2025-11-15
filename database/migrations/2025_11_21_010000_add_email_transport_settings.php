<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('notification.email_username', null);
        $this->migrator->add('notification.email_password', null);
        $this->migrator->add('notification.email_encryption', null);
        $this->migrator->add('notification.email_port', null);
        $this->migrator->add('notification.email_host', null);
    }

    public function down(): void
    {
        $this->migrator->delete('notification.email_username');
        $this->migrator->delete('notification.email_password');
        $this->migrator->delete('notification.email_encryption');
        $this->migrator->delete('notification.email_port');
        $this->migrator->delete('notification.email_host');
    }
};
