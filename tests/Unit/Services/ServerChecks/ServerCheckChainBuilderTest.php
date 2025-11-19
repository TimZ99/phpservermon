<?php

use App\Jobs\FinalizeServerCheckRun;
use App\Jobs\RunCurl;
use App\Jobs\ServerChecks\Headers;
use App\Jobs\ServerChecks\StatusCode;
use App\Models\Server;
use App\Services\ServerChecks\ServerCheckChainBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

it('builds a job chain with curl, enabled checks and finalize job', function () {
    $server = Server::factory()->create([
        'check_settings' => [
            'StatusCode' => ['enabled' => true],
            'Headers' => ['enabled' => true],
        ],
    ]);

    $builder = app(ServerCheckChainBuilder::class);
    $jobs = $builder->build($server, 'run-123');

    expect($jobs)->toHaveCount(1 + 2 + 1);
    expect($jobs[0])->toBeInstanceOf(RunCurl::class);
    expect($jobs[1])->toBeInstanceOf(StatusCode::class);
    expect($jobs[2])->toBeInstanceOf(Headers::class);
    expect($jobs[3])->toBeInstanceOf(FinalizeServerCheckRun::class);
});

it('skips unknown check jobs and logs a warning', function () {
    config()->set('server-checks.StatusCode.job', 'Unknown\\Job');

    $server = Server::factory()->create([
        'check_settings' => [
            'StatusCode' => ['enabled' => true],
        ],
    ]);

    Log::spy();

    $builder = app(ServerCheckChainBuilder::class);
    $jobs = $builder->build($server, 'run-xyz');

    // Only RunCurl and Finalize jobs should remain.
    expect($jobs)->toHaveCount(2);
    expect($jobs[0])->toBeInstanceOf(RunCurl::class);
    expect($jobs[1])->toBeInstanceOf(FinalizeServerCheckRun::class);

    Log::shouldHaveReceived('warning')
        ->once()
        ->with('Attempted to queue unknown server check', \Mockery::subset(['check' => 'StatusCode']));
});
