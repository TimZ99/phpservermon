<?php

use App\Models\Server;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

it('updates server check settings through the edit form', function () {

    $user = User::factory()->create();
    $user->forceFill(['scopes' => ['server:manage:*']])->save();

    $server = Server::factory()->create();
    $server->users()->attach($user);

    actingAs($user);

    $payload = [
        'name' => $server->name,
        'ip' => $server->ip,
        'port' => $server->port,
        'users' => [$user->id],
        'check_settings' => [
            'StatusCode' => ['enabled' => '1'],
            'SSL_active' => ['enabled' => '0'],
            'SSL_certificate_valid' => ['enabled' => '1'],
            'SSL_expiration' => ['enabled' => '1', 'days' => 10],
            'ContentRegex' => ['enabled' => '1', 'pattern' => '/OK/'],
            'Latency' => ['enabled' => '1', 'warning_ms' => 700, 'fail_ms' => 2000],
            'Headers' => ['enabled' => '1', 'required' => "X-Test:42\nX-Empty"],
        ],
    ];

    $response = patch(route('server.update', $server), $payload);

    $response->assertRedirect(route('server.show', $server));

    $server->refresh();
    expect(data_get($server->check_settings, 'SSL_active.enabled'))->toBeFalse()
        ->and(data_get($server->check_settings, 'SSL_expiration.input.days'))->toBe(10)
        ->and(data_get($server->check_settings, 'ContentRegex.input.pattern'))->toBe('/OK/')
        ->and(data_get($server->check_settings, 'Latency.input.warning_ms'))->toBe(700)
        ->and(data_get($server->check_settings, 'Latency.input.fail_ms'))->toBe(2000)
        ->and(data_get($server->check_settings, 'Headers.input.required'))->toMatchArray([
            'X-Test' => '42',
            'X-Empty' => null,
        ]);
});
