<?php

use App\Models\Server;
use App\Services\ServerChecks\CheckSettingsResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns enabled check names optionally limited', function () {
    $server = Server::factory()->create([
        'check_settings' => [
            'StatusCode' => ['enabled' => true],
            'Latency' => ['enabled' => false],
            'Headers' => ['enabled' => true],
        ],
    ]);

    $resolver = app(CheckSettingsResolver::class);

    expect($resolver->enabledChecks($server))->toBe(['StatusCode', 'Headers']);
    expect($resolver->enabledChecks($server, ['Headers']))->toBe(['Headers']);
});

it('returns configuration array for a specific check', function () {
    $server = Server::factory()->create([
        'check_settings' => [
            'StatusCode' => ['enabled' => true, 'custom' => 'value'],
        ],
    ]);

    $resolver = app(CheckSettingsResolver::class);

    expect($resolver->configFor($server, 'StatusCode'))->toBe(['enabled' => true, 'custom' => 'value']);
    expect($resolver->configFor($server, 'Missing'))->toBe([]);
});

it('ignores invalid or missing check settings', function () {
    $server = Server::factory()->create([
        'check_settings' => [
            'StatusCode' => true,
            'Headers' => ['enabled' => false],
        ],
    ]);

    $resolver = app(CheckSettingsResolver::class);

    expect($resolver->enabledChecks($server))->toBe([]);
});
