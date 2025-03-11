<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class NotificationSettings extends Settings
{
    public ?string $telegram_bot_token = null;

    public static function group(): string
    {
        return 'notification';
    }
}
