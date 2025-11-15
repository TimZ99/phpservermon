<?php

use App\Services\ServerChecks\ServerCheckRegistry;
use Illuminate\Support\Facades\Config;

it('returns all configured checks', function () {
    $registry = new ServerCheckRegistry;

    $all = $registry->all();

    expect($all)->toBeArray()
        ->and($all)->toHaveKey('StatusCode')
        ->and($all['StatusCode']['job'])->toBe(App\Jobs\ServerChecks\StatusCode::class);
});

it('builds defaults from job classes', function () {
    $registry = new ServerCheckRegistry;

    $defaults = $registry->defaults();

    expect($defaults['StatusCode']['enabled'])->toBeTrue()
        ->and($defaults['SSL_expiration']['input']['days'])->toBe(5)
        ->and($defaults['ContentRegex']['input']['pattern'])->toBe('/.+/');
});

it('falls back to disabled defaults when job has no default method', function () {
    Config::set('server-checks.testDummy', [
        'job' => stdClass::class,
        'description' => 'Dummy',
    ]);

    $registry = new ServerCheckRegistry;

    expect($registry->defaults()['testDummy'])->toMatchArray(['enabled' => false]);
});
