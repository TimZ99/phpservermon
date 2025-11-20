<?php

use App\Providers\AppServiceProvider;
use Illuminate\Database\Eloquent\Model;

function setAppEnvironment(string $environment): void
{
    $app = app();
    $app->detectEnvironment(fn () => $environment);
}

it('registers telescope provider when running locally', function () {
    $previous = app()->environment();
    setAppEnvironment('local');

    $provider = new AppServiceProvider(app());
    $provider->register();

    expect(app()->getLoadedProviders())->toHaveKey(\Laravel\Telescope\TelescopeServiceProvider::class);

    setAppEnvironment($previous);
});

it('enables strict attribute discarding prevention during boot', function () {
    $previous = app()->environment();
    setAppEnvironment('local');

    $ref = new ReflectionProperty(Model::class, 'modelsShouldPreventSilentlyDiscardingAttributes');
    $ref->setAccessible(true);
    $ref->setValue(false);

    $provider = new AppServiceProvider(app());
    $provider->boot();

    expect($ref->getValue())->toBeTrue();

    setAppEnvironment($previous);
});
