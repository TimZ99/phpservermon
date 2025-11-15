<?php

use App\Models\Server;
use App\Services\ServerChecks\RunServerCheckService;
use App\Services\ServerChecks\ServerCheckOrchestrator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

beforeEach(function () {
    Session::flush();
});

it('dispatches through orchestrator and stores run ids in session', function () {
    $servers = Server::factory()->count(2)->create();
    $expectedRunIds = [
        $servers[0]->id => (string) Str::uuid(),
        $servers[1]->id => (string) Str::uuid(),
    ];

    $orchestrator = \Mockery::mock(ServerCheckOrchestrator::class);
    $orchestrator->shouldReceive('dispatch')
        ->once()
        ->withArgs(function ($arg) use ($servers) {
            return collect($arg)->pluck('id')->diff($servers->pluck('id'))->isEmpty();
        })
        ->andReturn($expectedRunIds);

    $action = new RunServerCheckService($orchestrator);
    $result = $action->handle($servers);

    expect($result)->toBe($expectedRunIds);
    foreach ($expectedRunIds as $serverId => $runId) {
        expect(session("server_run.{$serverId}"))->toBe($runId);
    }
});

it('can skip session tracking when disabled', function () {
    $server = Server::factory()->create();
    $runId = (string) Str::uuid();

    $orchestrator = \Mockery::mock(ServerCheckOrchestrator::class);
    $orchestrator->shouldReceive('dispatch')
        ->once()
        ->andReturn([$server->id => $runId]);

    $action = new RunServerCheckService($orchestrator);
    $action->handle([$server], trackSession: false);

    expect(session()->all())->not->toHaveKey("server_run.{$server->id}");
});

afterEach(function () {
    \Mockery::close();
});
