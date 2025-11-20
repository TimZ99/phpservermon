<?php

use App\Models\Server;
use App\Models\User;
use App\Services\ServerChecks\RunServerCheckService;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('runs a single server check and redirects back to the server page', function () {
    $user = User::factory()->create(['scopes' => ['server:manage:*']]);
    $server = Server::factory()->create();

    actingAs($user);

    $service = \Mockery::mock(RunServerCheckService::class);
    $service->shouldReceive('handle')
        ->once()
        ->with(\Mockery::on(function ($servers) use ($server) {
            $collection = $servers instanceof \Illuminate\Support\Collection ? $servers : collect($servers);

            return $collection->count() === 1 && $collection->first()->is($server);
        }))
        ->andReturn([$server->id => 'run-id']);

    $this->instance(RunServerCheckService::class, $service);

    get(route('server.runChecks', $server))
        ->assertRedirect(route('server.show', $server))
        ->assertSessionHas('check_dispatched', true);
});

it('runs a batch of server checks for the authenticated user', function () {
    $user = User::factory()->create(['scopes' => ['server:manage:*']]);
    $servers = Server::factory()->count(2)->create();
    $user->servers()->attach($servers->pluck('id'));

    actingAs($user);

    $service = \Mockery::mock(RunServerCheckService::class);
    $service->shouldReceive('handle')
        ->once()
        ->with(\Mockery::on(function ($collection) use ($servers) {
            $collection = $collection instanceof \Illuminate\Support\Collection ? $collection : collect($collection);

            return $collection->count() === 2
                && $collection->pluck('id')->diff($servers->pluck('id'))->isEmpty();
        }))
        ->andReturn([]);

    $this->instance(RunServerCheckService::class, $service);

    get(route('server.runBatch'))
        ->assertRedirect(route('server.monitor'))
        ->assertSessionHas('check_dispatched', true);
});
