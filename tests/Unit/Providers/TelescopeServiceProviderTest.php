<?php

use App\Providers\TelescopeServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;

function resetTelescopeState(): void
{
    foreach (['hiddenRequestParameters', 'hiddenRequestHeaders', 'filterUsing'] as $property) {
        $ref = new ReflectionProperty(Telescope::class, $property);
        $ref->setAccessible(true);
        $ref->setValue([]);
    }
}

beforeEach(function () {
    resetTelescopeState();
});

afterEach(function () {
    App::detectEnvironment(fn () => 'testing');
    resetTelescopeState();
});

it('hides sensitive request details when not local', function () {
    App::detectEnvironment(fn () => 'production');

    $provider = new TelescopeServiceProvider(app());
    $provider->register();

    $params = new ReflectionProperty(Telescope::class, 'hiddenRequestParameters');
    $params->setAccessible(true);
    $headers = new ReflectionProperty(Telescope::class, 'hiddenRequestHeaders');
    $headers->setAccessible(true);

    expect($params->getValue())->toContain('_token');
    expect($headers->getValue())->toContain('cookie', 'x-csrf-token', 'x-xsrf-token');
});

it('does not hide request details when running locally', function () {
    App::detectEnvironment(fn () => 'local');

    $provider = new TelescopeServiceProvider(app());
    $provider->register();

    $params = new ReflectionProperty(Telescope::class, 'hiddenRequestParameters');
    $params->setAccessible(true);

    expect($params->getValue())->toBe([]);
});

it('filters entries for non-local environments', function () {
    App::detectEnvironment(fn () => 'production');

    $provider = new TelescopeServiceProvider(app());
    $provider->register();

    $filters = new ReflectionProperty(Telescope::class, 'filterUsing');
    $filters->setAccessible(true);
    $callback = last($filters->getValue());

    $failedJob = new IncomingEntry(['status' => 'failed']);
    $failedJob->type = EntryType::JOB;
    expect($callback($failedJob))->toBeTrue();

    $successfulRequest = new IncomingEntry(['status' => 'success']);
    $successfulRequest->type = EntryType::REQUEST;
    expect($callback($successfulRequest))->toBeFalse();
});

it('defines the viewTelescope gate', function () {
    $provider = new TelescopeServiceProvider(app());
    $method = new \ReflectionMethod(TelescopeServiceProvider::class, 'gate');
    $method->setAccessible(true);
    $method->invoke($provider);

    $user = (object) ['email' => 'user@example.com'];
    expect(Gate::forUser($user)->allows('viewTelescope'))->toBeFalse();
});
