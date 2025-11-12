<?php

use App\Models\NotificationPreference;
use App\Models\Server;
use App\Models\User;
use App\Notifications\Channels\Telegram\TelegramChannel;
use App\Services\Notifications\NotificationPreferenceService;
use App\Settings\NotificationSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $settings = new NotificationSettings;
    $settings->email_global_enabled = true;
    $settings->telegram_global_enabled = true;
    app()->instance(NotificationSettings::class, $settings);
});

function preferenceService(): NotificationPreferenceService
{
    return app(NotificationPreferenceService::class);
}

it('returns default channels when user has contact info', function () {
    $user = User::factory()->create(['email' => 'demo@example.com', 'telegram_user_id' => 123]);
    $server = Server::factory()->create();

    $channels = preferenceService()->channelsFor($user, $server, '__OVERALLSTATUS__');

    expect($channels)->toContain('mail', TelegramChannel::class);
});

it('honors per-server mute preferences', function () {
    $user = User::factory()->create(['email' => 'demo@example.com']);
    $server = Server::factory()->create();

    NotificationPreference::create([
        'user_id' => $user->id,
        'server_id' => $server->id,
        'check_name' => '__OVERALLSTATUS__',
        'channel' => 'mail',
        'enabled' => false,
    ]);

    $channels = preferenceService()->channelsFor($user, $server, '__OVERALLSTATUS__');

    expect($channels)->not->toContain('mail');
});

it('treats muted-until preferences as disabled until expiration', function () {
    $user = User::factory()->create(['email' => 'demo@example.com']);
    $server = Server::factory()->create();

    NotificationPreference::create([
        'user_id' => $user->id,
        'server_id' => null,
        'check_name' => null,
        'channel' => 'mail',
        'enabled' => true,
        'muted_until' => Carbon::now()->addHour(),
    ]);

    $channels = preferenceService()->channelsFor($user, $server, '__OVERALLSTATUS__');

    expect($channels)->not->toContain('mail');
});
