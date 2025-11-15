<?php

use App\Models\User;
use App\Providers\SettingsConfigServiceProvider;
use App\Settings\NotificationSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

uses(RefreshDatabase::class);

function configManagerUser(): User
{
    $user = User::factory()->create();
    $user->scopes = ['config:manage'];
    $user->save();

    return $user;
}

function configPayload(array $overrides = []): array
{
    return array_merge([
        'locale' => 'en',
        'timezone' => 'UTC',
        'check_history_retention_days' => 7,
        'email_global_enabled' => true,
        'email_from_name' => 'Ops Bot',
        'email_from_address' => 'ops@example.test',
        'email_host' => 'smtp.example.test',
        'email_port' => 465,
        'email_encryption' => 'ssl',
        'email_username' => 'ops@example.test',
        'email_password' => 'super-secret',
        'telegram_global_enabled' => false,
        'telegram_bot_token' => null,
    ], $overrides);
}

it('stores smtp transport settings from the config page', function () {
    actingAs(configManagerUser());

    patch(route('config.update'), configPayload())
        ->assertRedirect(route('config.edit'))
        ->assertSessionHas('success');

    $settings = app(NotificationSettings::class);

    expect($settings->email_host)->toBe('smtp.example.test')
        ->and($settings->email_port)->toBe(465)
        ->and($settings->email_encryption)->toBe('ssl')
        ->and($settings->email_username)->toBe('ops@example.test')
        ->and($settings->email_password)->toBe('super-secret');

    // Boot provider to sync config values
    (new SettingsConfigServiceProvider(app()))->boot();

    expect(config('mail.mailers.smtp.host'))->toBe('smtp.example.test')
        ->and(config('mail.mailers.smtp.port'))->toBe(465)
        ->and(config('mail.mailers.smtp.username'))->toBe('ops@example.test')
        ->and(config('mail.mailers.smtp.password'))->toBe('super-secret')
        ->and(config('mail.mailers.smtp.scheme'))->toBe('ssl');
});

it('rejects unsupported encryption protocols', function () {
    actingAs(configManagerUser());

    patch(route('config.update'), configPayload([
        'email_encryption' => 'starttls',
    ]))
        ->assertSessionHasErrors('email_encryption');
});
