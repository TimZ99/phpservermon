<?php

use App\Enums\QueueName;
use App\Jobs\FinalizeServerCheckRun;
use App\Jobs\RunCurl;
use App\Jobs\ServerChecks\StatusCode;
use App\Models\Server;
use App\Services\Queue\QueueHeartbeatService;
use App\Services\ServerChecks\ServerCheckOrchestrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

afterEach(function () {
    \Mockery::close();
});

it('dispatches chains on the curl queue when workers are alive', function () {
    Bus::fake();
    $servers = Server::factory()->count(2)->create([
        'check_settings' => [
            'StatusCode' => ['enabled' => true],
        ],
    ]);

    $heartbeat = \Mockery::mock(QueueHeartbeatService::class);
    $heartbeat->shouldReceive('isAlive')->once()->with(QueueName::CURL)->andReturnTrue();
    app()->instance(QueueHeartbeatService::class, $heartbeat);

    $runIds = app(ServerCheckOrchestrator::class)->dispatch($servers);
    expect($runIds)->toHaveCount(2);

    Bus::assertChained([
        function (RunCurl $job) {
            return $job->queue === QueueName::CURL->value;
        },
        StatusCode::class,
        FinalizeServerCheckRun::class,
    ]);
});

it('falls back to sync connection when heartbeat is stale', function () {
    Bus::fake();
    $servers = Server::factory()->count(1)->create([
        'check_settings' => [
            'StatusCode' => ['enabled' => true],
        ],
    ]);

    $heartbeat = \Mockery::mock(QueueHeartbeatService::class);
    $heartbeat->shouldReceive('isAlive')->once()->with(QueueName::CURL)->andReturnFalse();
    app()->instance(QueueHeartbeatService::class, $heartbeat);

    app(ServerCheckOrchestrator::class)->dispatch($servers);

    Bus::assertDispatched(RunCurl::class, function (RunCurl $job) {
        return $job->connection === 'sync' && $job->queue === QueueName::CURL->value;
    });
});
