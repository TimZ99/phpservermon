<?php

use App\Jobs\ServerChecks\BaseServerCheckJob;
use App\Services\ServerChecks\ServerCheckRunStore;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DummyServerCheckJob extends BaseServerCheckJob
{
    public bool $performed = false;

    protected function perform(array $payload, array $settings): void
    {
        $this->performed = true;
    }

    protected function checkName(): string
    {
        return 'DummyCheck';
    }
}

function putDummyPayload(array $settings): array
{
    $runId = (string) Str::uuid();
    $serverId = (string) Str::uuid();
    $store = new ServerCheckRunStore;
    $store->put($runId, [
        'check_settings' => [
            'DummyCheck' => $settings,
        ],
    ]);

    return [$runId, $serverId, $store];
}

it('logs a warning when payload is missing', function () {
    Log::shouldReceive('warning')->once();
    $job = new DummyServerCheckJob((string) Str::uuid(), (string) Str::uuid());

    $job->handle(new ServerCheckRunStore);

    expect($job->performed)->toBeFalse();
});

it('does not run when check is disabled', function () {
    Log::shouldReceive('debug')->once();
    [$runId, $serverId, $store] = putDummyPayload(['enabled' => false]);

    $job = new DummyServerCheckJob($serverId, $runId);
    $job->handle($store);

    expect($job->performed)->toBeFalse();
});

it('runs perform when payload exists and enabled', function () {
    [$runId, $serverId, $store] = putDummyPayload(['enabled' => true]);

    $job = new DummyServerCheckJob($serverId, $runId);
    $job->handle($store);

    expect($job->performed)->toBeTrue();
});

it('returns disabled defaults and normalizes status labels', function () {
    expect(DummyServerCheckJob::defaults())->toBe(['enabled' => false]);

    $job = new DummyServerCheckJob((string) Str::uuid(), (string) Str::uuid());
    $reflection = new \ReflectionMethod($job, 'normalizeStatus');
    $reflection->setAccessible(true);

    expect($reflection->invoke($job, 'ERROR'))->toBe('fail')
        ->and($reflection->invoke($job, 'warning'))->toBe('warning')
        ->and($reflection->invoke($job, 'ok'))->toBe('success');
});
