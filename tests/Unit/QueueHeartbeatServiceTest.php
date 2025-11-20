<?php

use App\Services\Queue\QueueHeartbeatService;
use Carbon\Carbon;
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

it('respects explicit timestamps when recording heartbeats', function () {
    $service = new QueueHeartbeatService;
    $moment = Carbon::now()->subMinutes(2);

    $service->record('curl', $moment);

    $lastBeat = $service->lastBeat('curl');
    expect($lastBeat)->not->toBeNull()
        ->and($lastBeat->timestamp)->toBe($moment->timestamp);
});

it('determines queue liveness based on heartbeat age', function () {
    $service = new QueueHeartbeatService;
    $service->record('curl', now()->subSeconds(30));

    expect($service->isAlive('curl', thresholdSeconds: 60))->toBeTrue()
        ->and($service->isAlive('curl', thresholdSeconds: 10))->toBeFalse();
});

it('stores queue heartbeat for at least ten minutes', function () {

    Carbon::setTestNow(now());

    $service = new QueueHeartbeatService;
    $service->record('curl');

    $cacheKey = (new \ReflectionClass($service))->getConstant('CACHE_KEY_PREFIX').'curl';

    $lastBeat = Cache::get($cacheKey);
    expect($lastBeat)->not->toBeNull();

    Carbon::setTestNow(now()->addMinutes(9)->addSeconds(59));
    expect(Cache::get($cacheKey))->toBe($lastBeat);

    Carbon::setTestNow(now()->addSeconds(2));
    expect(Cache::get($cacheKey))->toBeNull();
});

it('returns null when no heartbeat has been recorded', function () {
    $service = new QueueHeartbeatService;

    expect($service->lastBeat('curl'))->toBeNull();
    expect($service->isAlive('curl'))->toBeFalse();
});
