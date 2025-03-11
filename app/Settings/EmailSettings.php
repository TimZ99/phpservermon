<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class EmailSettings extends Settings
{
    public ?string $from_address = null;

    public ?string $from_name = null;

    public static function group(): string
    {
        return 'email';
    }
}
