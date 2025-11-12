<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.check_history_retention_days', 7);
    }

    public function down(): void
    {
        $this->migrator->delete('general.check_history_retention_days');
    }
};
