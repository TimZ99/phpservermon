<?php

use App\Models\Server;
use App\Services\ServerChecks\RunServerCheckService;
use App\Services\ServerChecks\ServerCheckOrchestrator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

afterEach(function () {
    \Mockery::close();
    session()->flush();
});

it('returns empty array and does not dispatch when there are no servers', function () {
    $orchestrator = \Mockery::mock(ServerCheckOrchestrator::class);
    $orchestrator->shouldReceive('dispatch')->never();
    app()->instance(ServerCheckOrchestrator::class, $orchestrator);

    $service = app(RunServerCheckService::class);

    expect($service->handle([]))->toBe([]);
});

it('dispatches through orchestrator and stores run ids in session when tracking', function () {
    $server = Server::factory()->create();
    $runIds = [$server->id => 'run-abc'];

    $orchestrator = \Mockery::mock(ServerCheckOrchestrator::class);
    $orchestrator->shouldReceive('dispatch')
        ->once()
        ->withArgs(fn ($collection) => $collection->count() === 1 && $collection->first()->is($server))
        ->andReturn($runIds);
    app()->instance(ServerCheckOrchestrator::class, $orchestrator);

    $service = app(RunServerCheckService::class);

    $result = $service->handle([$server]);

    expect($result)->toBe($runIds);
    expect(session("server_run.{$server->id}"))->toBe('run-abc');
});

it('can skip session tracking when requested', function () {
    $server = Server::factory()->create();
    $runIds = [$server->id => 'run-def'];

    $orchestrator = \Mockery::mock(ServerCheckOrchestrator::class);
    $orchestrator->shouldReceive('dispatch')->once()->andReturn($runIds);
    app()->instance(ServerCheckOrchestrator::class, $orchestrator);

    $service = app(RunServerCheckService::class);

    $service->handle([$server], trackSession: false);

    expect(session()->all())->not->toHaveKey("server_run.{$server->id}");
});
