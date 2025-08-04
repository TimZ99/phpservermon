<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class EmailSettings extends Settings
{
    public ?string $from_address = null;

    public ?string $from_name = null;

    public bool $email_notifications_enabled = false;

    public static function group(): string
    {
        return 'mail';
    }
}
