<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public ?string $default_locale = null;

    public ?string $timezone = null;

    public int $check_history_retention_days = 7;

    public static function group(): string
    {
        return 'general';
    }
}
