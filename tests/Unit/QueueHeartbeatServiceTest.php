<?php

use App\Services\Queue\QueueHeartbeatService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::clear();
});

it('records and retrieves heartbeats', function () {
    $service = new QueueHeartbeatService;

    $service->record('curl', now()->subSeconds(10));

    $lastBeat = $service->lastBeat('curl');
    expect($lastBeat)->not->toBeNull()
        ->and($lastBeat->diffInSeconds(now(), false))->toBeLessThanOrEqual(11);
});

it('determines queue liveness based on heartbeat age', function () {
    $service = new QueueHeartbeatService;
    $service->record('curl', now()->subSeconds(30));

    expect($service->isAlive('curl', thresholdSeconds: 60))->toBeTrue()
        ->and($service->isAlive('curl', thresholdSeconds: 10))->toBeFalse();
});
