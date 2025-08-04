<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class NotificationSettings extends Settings
{
    public ?string $telegram_bot_token = null;

    public bool $telegram_notifications_enabled = false;

    public static function group(): string
    {
        return 'notification';
    }
}
