<?php

use App\Models\Server;
use App\Models\User;
use App\Notifications\Channels\Telegram\TelegramChannel as ModernTelegramChannel;
use App\Notifications\Channels\TelegramChannel as LegacyTelegramChannel;
use App\Notifications\Channels\TelegramChannelUser;
use App\Notifications\Messages\DynamicNotification;
use App\Notifications\ServerStatusChanged;
use App\Settings\NotificationSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    NotificationSettings::fake([
        'email_global_enabled' => true,
        'telegram_global_enabled' => true,
        'telegram_bot_token' => 'token-123',
    ]);
});

afterEach(function () {
    \Mockery::close();
});

it('respects explicit channels and builds messages for dynamic notification', function () {
    $notification = new DynamicNotification('server.alert', ['name' => 'demo'], ['mail', 'sms', 'mail']);
    $channels = $notification->via((object) ['email' => 'demo@example.com']);
    expect($channels)->toBe(['mail', 'sms']);

    $mail = $notification->toMail((object) []);
    expect($mail->subject)->toBe('notifications.server.alert.subject')
        ->and($mail->introLines)->toContain('notifications.server.alert.message');

    expect($notification->toSms((object) []))->toBe('notifications.server.alert.sms_text');
});

it('derives channels from notification settings and notifiable info', function () {
    $notifiable = User::factory()->make([
        'email' => 'ops@example.com',
        'telegram_user_id' => 456,
    ]);
    $notification = new DynamicNotification('event');

    $channels = $notification->via($notifiable);

    expect($channels)->toContain('mail')
        ->and($channels)->toContain(ModernTelegramChannel::class);
});

it('builds server status messages for mail and telegram', function () {
    $server = Server::factory()->make(['name' => 'Demo Server']);
    $summary = [
        'failures' => [
            ['name' => 'Latency', 'message' => 'Too slow'],
        ],
        'warnings' => [
            ['name' => 'SSL', 'message' => 'Expiring soon'],
        ],
    ];

    $notification = new ServerStatusChanged($server, 'warning', $summary, ['mail', ModernTelegramChannel::class]);

    expect($notification->via((object) []))->toBe(['mail', ModernTelegramChannel::class]);

    $mail = $notification->toMail((object) []);
    $summaryLines = $mail->introLines;

    expect($mail->subject)->toBe('Demo Server status is WARNING')
        ->and($summaryLines)->toContain('Demo Server is now WARNING.');

    $lastLine = end($summaryLines);
    expect($lastLine)->toContain('FAIL: Latency – Too slow')
        ->and($lastLine)->toContain('WARN: SSL – Expiring soon');

    $telegram = $notification->toTelegram((object) []);
    expect($telegram)->toContain('*Demo Server* is now *WARNING*')
        ->and($telegram)->toContain('FAIL: Latency – Too slow')
        ->and($telegram)->toContain('WARN: SSL – Expiring soon');
});

it('falls back to default summary text when no entries recorded', function () {
    $server = Server::factory()->make(['name' => 'Demo']);
    $notification = new ServerStatusChanged($server, 'success', [], ['mail']);

    $mail = $notification->toMail((object) []);
    expect($mail->introLines)->toContain('No detailed check results were recorded.');
});

class CustomTelegramNotification extends Notification
{
    public function toTelegram(object $notifiable): string
    {
        $chatId = $notifiable->routeNotificationFor('telegram', $this) ?? 'user';

        return 'Hello '.$chatId;
    }
}

class DataOnlyNotification extends Notification
{
    public array $data = ['text' => 'Fallback text'];
}

it('sends telegram messages using toTelegram when available', function () {
    $channel = new ModernTelegramChannel;
    $notifiable = User::factory()->make(['telegram_user_id' => 999]);
    $notification = new CustomTelegramNotification;

    $telegram = \Mockery::mock('alias:NotificationChannels\Telegram\TelegramMessage');
    $telegram->shouldReceive('create')->once()->with('Hello 999')->andReturnSelf();
    $telegram->shouldReceive('token')->once()->with('token-123')->andReturnSelf();
    $telegram->shouldReceive('to')->once()->with(999)->andReturnSelf();
    $telegram->shouldReceive('send')->once();

    $channel->send($notifiable, $notification);
});

it('falls back to notification data text when toTelegram is missing', function () {
    $channel = new ModernTelegramChannel;
    $notifiable = User::factory()->make(['telegram_user_id' => 456]);
    $notification = new DataOnlyNotification;

    $telegram = \Mockery::mock('alias:NotificationChannels\Telegram\TelegramMessage');
    $telegram->shouldReceive('create')->once()->with('Fallback text')->andReturnSelf();
    $telegram->shouldReceive('token')->once()->with('token-123')->andReturnSelf();
    $telegram->shouldReceive('to')->once()->with(456)->andReturnSelf();
    $telegram->shouldReceive('send')->once();

    $channel->send($notifiable, $notification);
});

it('skips telegram sending when routeNotificationFor returns null', function () {
    $channel = new ModernTelegramChannel;
    $notifiable = User::factory()->make(['telegram_user_id' => null]);
    $notification = new CustomTelegramNotification;

    \Mockery::mock('alias:NotificationChannels\Telegram\TelegramMessage')
        ->shouldReceive('create')->never();

    $channel->send($notifiable, $notification);
});

it('uses default telegram message when notification has no data', function () {
    $channel = new ModernTelegramChannel;
    $notifiable = User::factory()->make(['telegram_user_id' => 321]);
    $notification = new class extends Notification {};

    $telegram = \Mockery::mock('alias:NotificationChannels\Telegram\TelegramMessage');
    $telegram->shouldReceive('create')->once()->with('You have a new notification.')->andReturnSelf();
    $telegram->shouldReceive('token')->once()->with('token-123')->andReturnSelf();
    $telegram->shouldReceive('to')->once()->with(321)->andReturnSelf();
    $telegram->shouldReceive('send')->once();

    $channel->send($notifiable, $notification);
});

it('sends notifications using legacy telegram channel when user id available', function () {
    $channel = new LegacyTelegramChannel;
    $notifiable = User::factory()->make(['telegram_user_id' => 123]);
    $notification = new DataOnlyNotification;

    $telegram = \Mockery::mock('alias:NotificationChannels\Telegram\TelegramMessage');
    $telegram->shouldReceive('create')->once()->with('Fallback text')->andReturnSelf();
    $telegram->shouldReceive('token')->once()->with('token-123')->andReturnSelf();
    $telegram->shouldReceive('to')->once()->with(123)->andReturnSelf();
    $telegram->shouldReceive('send')->once();

    $channel->send($notifiable, $notification);
});

it('does not send legacy telegram notification when user lacks chat id', function () {
    $channel = new LegacyTelegramChannel;
    $notifiable = User::factory()->make(['telegram_user_id' => null]);
    $notification = new DataOnlyNotification;

    \Mockery::mock('alias:NotificationChannels\Telegram\TelegramMessage')
        ->shouldReceive('create')->never();

    $channel->send($notifiable, $notification);
});

it('routes telegram channel user notifications to telegram user id', function () {
    $user = new TelegramChannelUser;
    $user->telegram_user_id = 777;

    expect($user->routeNotificationForTelegram())->toBe(777);
});
