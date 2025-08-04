<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class NotificationSettings extends Settings
{
    public bool $email_global_enabled = false;
    public ?string $email_from_address = null;
    public ?string $email_from_name = null;
    
    public bool $telegram_global_enabled = false;
    public ?string $telegram_bot_token = null;
    public static function group(): string
    {
        return 'notification';
    }
}
