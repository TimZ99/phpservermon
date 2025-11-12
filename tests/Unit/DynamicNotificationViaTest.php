<?php

use App\Models\User;
use App\Notifications\Channels\Telegram\TelegramChannel;
use App\Notifications\Messages\DynamicNotification;
use App\Settings\NotificationSettings;

// No explicit expect() import needed; Pest exposes a global expect() helper.

function mockSettings(bool $email, bool $telegram): void
{
    $settings = new NotificationSettings;
    $settings->email_global_enabled = $email;
    $settings->telegram_global_enabled = $telegram;
    // token/address/name are irrelevant for via() logic
    app()->instance(NotificationSettings::class, $settings);
}

it('resolves email only when email enabled and present', function () {
    $user = User::factory()->make([
        'email' => 'user@example.com',
        'telegram_user_id' => null,
    ]);

    mockSettings(email: true, telegram: false);

    $notification = new DynamicNotification('test_message', ['text' => 'hi']);

    $via = $notification->via($user);

    expect($via)->toContain('mail')
        ->and($via)->not->toContain(TelegramChannel::class);
});

it('resolves telegram only when telegram enabled and present', function () {
    $user = User::factory()->make([
        'email' => null,
        'telegram_user_id' => 123456789,
    ]);

    mockSettings(email: false, telegram: true);

    $notification = new DynamicNotification('test_message', ['text' => 'hi']);

    $via = $notification->via($user);

    expect($via)->toContain(TelegramChannel::class)
        ->and($via)->not->toContain('mail');
});

it('resolves both when both enabled and present', function () {
    $user = User::factory()->make([
        'email' => 'user@example.com',
        'telegram_user_id' => 123456789,
    ]);

    mockSettings(email: true, telegram: true);

    $notification = new DynamicNotification('test_message', ['text' => 'hi']);

    $via = $notification->via($user);

    expect($via)->toContain('mail')
        ->and($via)->toContain(TelegramChannel::class);
});

it('resolves none when disabled or missing contact info', function () {
    $user = User::factory()->make([
        'email' => null,
        'telegram_user_id' => null,
    ]);

    mockSettings(email: true, telegram: true);

    $notification = new DynamicNotification('test_message', ['text' => 'hi']);

    $via = $notification->via($user);

    expect($via)->toBeArray()->toBeEmpty();
});
