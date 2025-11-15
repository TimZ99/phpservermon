<?php

namespace App\Settings;

use Spatie\LaravelSettings\Attributes\Encrypted;
use Spatie\LaravelSettings\Settings;

class NotificationSettings extends Settings
{
    public bool $email_global_enabled = false;

    public ?string $email_from_address = null;

    public ?string $email_from_name = null;

    public ?string $email_username = null;

    #[Encrypted]
    public ?string $email_password = null;

    public ?string $email_encryption = null;

    public ?int $email_port = null;

    public ?string $email_host = null;

    public bool $telegram_global_enabled = false;

    #[Encrypted]
    public ?string $telegram_bot_token = null;

    public static function group(): string
    {
        return 'notification';
    }
}
