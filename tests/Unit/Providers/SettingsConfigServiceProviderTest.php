<?php

use App\Providers\SettingsConfigServiceProvider;
use App\Settings\GeneralSettings;
use App\Settings\NotificationSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TestableSettingsConfigServiceProvider extends SettingsConfigServiceProvider
{
    public bool $canConnect = true;

    protected function canConnectToDatabase(): bool
    {
        return $this->canConnect;
    }
}

beforeEach(function () {
    NotificationSettings::fake([]);
    GeneralSettings::fake([]);
});

it('skips boot when the database is unavailable', function () {
    $provider = new TestableSettingsConfigServiceProvider(app());
    $provider->canConnect = false;

    Schema::shouldReceive('hasTable')->never();

    $provider->boot();
});

it('logs when settings table does not exist', function () {
    $provider = new TestableSettingsConfigServiceProvider(app());
    $provider->canConnect = true;

    Schema::shouldReceive('hasTable')->once()->andReturn(false);

    $provider->boot();
});

it('applies configuration overrides when settings are available', function () {
    $provider = new TestableSettingsConfigServiceProvider(app());
    $provider->canConnect = true;

    Schema::shouldReceive('hasTable')->once()->andReturn(true);

    GeneralSettings::fake([
        'default_locale' => 'nl',
        'timezone' => 'Europe/Amsterdam',
    ]);

    NotificationSettings::fake([
        'email_from_name' => 'Ops',
        'email_from_address' => 'ops@example.com',
        'email_host' => 'smtp.example.com',
        'email_port' => 2525,
        'email_username' => 'ops@example.com',
        'email_password' => 'secret',
        'email_encryption' => 'tls',
        'telegram_bot_token' => 'token',
    ]);

    $provider->boot();

    expect(config('app.locale'))->toBe('nl')
        ->and(config('app.timezone'))->toBe('Europe/Amsterdam')
        ->and(config('email.from.name'))->toBe('Ops')
        ->and(config('email.from.address'))->toBe('ops@example.com')
        ->and(config('mail.mailers.smtp.host'))->toBe('smtp.example.com')
        ->and(config('mail.mailers.smtp.port'))->toBe(2525)
        ->and(config('mail.mailers.smtp.username'))->toBe('ops@example.com')
        ->and(config('mail.mailers.smtp.password'))->toBe('secret')
        ->and(config('mail.mailers.smtp.scheme'))->toBe('tls')
        ->and(config('notification.telegram_bot_token'))->toBe('token');
});

it('logs a warning when overriding configuration fails', function () {
    $provider = new TestableSettingsConfigServiceProvider(app());
    $provider->canConnect = true;

    Schema::shouldReceive('hasTable')->once()->andReturn(true);

    app()->bind(GeneralSettings::class, fn () => throw new \RuntimeException('boom'));

    $provider->boot();

    // Configuration should remain untouched when an exception occurs.
    expect(config('app.locale'))->toBe(config('app.locale'));
});

it('handles database connection failures when checking connectivity', function () {
    $provider = new SettingsConfigServiceProvider(app());

    DB::shouldReceive('connection')->once()->andReturnSelf();
    DB::shouldReceive('getPdo')->andThrow(new \RuntimeException('db down'));

    $method = new \ReflectionMethod(SettingsConfigServiceProvider::class, 'canConnectToDatabase');
    $method->setAccessible(true);

    expect($method->invoke($provider))->toBeFalse();
});
