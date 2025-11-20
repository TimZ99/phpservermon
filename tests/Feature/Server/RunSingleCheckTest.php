<?php

use App\Models\Server;
use App\Models\User;
use App\Services\ServerChecks\RunServerCheckService;
use App\Services\ServerChecks\ServerCheckRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

class FakeServerCheckRegistry extends ServerCheckRegistry
{
    public array $definitions = [
        'cpu' => ['description' => 'CPU load'],
        'memory' => ['description' => 'Memory usage'],
    ];

    public function all(): array
    {
        return $this->definitions;
    }
}

beforeEach(function () {
    app()->instance(ServerCheckRegistry::class, new FakeServerCheckRegistry(
        app(App\Services\Queue\QueueHeartbeatService::class),
        app(App\Services\ServerChecks\ServerCheckChainBuilder::class)
    ));
});

afterEach(function () {
    Mockery::close();
});

it('dispatches a single check when enabled', function () {
    $user = User::factory()->create(['scopes' => ['server:check:*']]);
    $server = Server::factory()->create([
        'check_settings' => [
            'cpu' => ['enabled' => true],
        ],
    ]);

    $mock = Mockery::mock(RunServerCheckService::class);
    $mock->shouldReceive('handle')
        ->once()
        ->with(
            Mockery::on(fn ($servers) => is_array($servers) && $servers[0]->is($server)),
            true,
            ['cpu']
        )
        ->andReturn([]);

    app()->instance(RunServerCheckService::class, $mock);

    actingAs($user);

    post(route('server.runCheck', [$server, 'cpu']))
        ->assertRedirect(route('server.show', $server))
        ->assertSessionHas('check_dispatched', true)
        ->assertSessionHas('check_name', 'cpu');
});

it('rejects running a disabled check', function () {
    $user = User::factory()->create(['scopes' => ['server:check:*']]);
    $server = Server::factory()->create([
        'check_settings' => [
            'cpu' => ['enabled' => false],
        ],
    ]);

    $mock = Mockery::mock(RunServerCheckService::class);
    $mock->shouldReceive('handle')->never();
    app()->instance(RunServerCheckService::class, $mock);

    actingAs($user);

    post(route('server.runCheck', [$server, 'cpu']))
        ->assertRedirect(route('server.show', $server))
        ->assertSessionHas('check_error');
});

it('returns 404 for unknown checks', function () {
    $user = User::factory()->create(['scopes' => ['server:check:*']]);
    $server = Server::factory()->create([
        'check_settings' => [
            'cpu' => ['enabled' => true],
        ],
    ]);

    actingAs($user);

    post(route('server.runCheck', [$server, 'disk']))
        ->assertNotFound();
});
