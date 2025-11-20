<?php

use App\Enums\QueueName;
use App\Jobs\Heartbeat\CurlWorkerHeartbeat;
use App\Services\Queue\QueueHeartbeatService;

it('records heartbeat for curl queue', function () {
    $job = new CurlWorkerHeartbeat;

    $service = \Mockery::mock(QueueHeartbeatService::class);
    $service->shouldReceive('record')
        ->once()
        ->with(QueueName::CURL);

    $job->handle($service);
});
