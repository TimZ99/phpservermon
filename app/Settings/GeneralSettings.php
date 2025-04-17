<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public ?string $default_locale = null;

    public ?string $timezone = null;

    public static function group(): string
    {
        return 'general';
    }
}
