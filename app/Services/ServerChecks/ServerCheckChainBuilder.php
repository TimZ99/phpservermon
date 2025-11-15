<?php

namespace App\Services\ServerChecks;

use App\Jobs\FinalizeServerCheckRun;
use App\Jobs\RunCurl;
use App\Models\Server;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ServerCheckChainBuilder
{
    public function __construct(
        private readonly CheckSettingsResolver $resolver
    ) {}

    /**
     * @param  array<string>  $limitToChecks
     * @return array<int, object>
     */
    public function build(Server $server, string $runId, array $limitToChecks = []): array
    {
        $checks = $this->resolver->enabledChecks($server, $limitToChecks);
        $jobs = [
            new RunCurl($server->id, $runId, $checks),
        ];

        foreach ($checks as $check) {
            $jobClass = Arr::get(config('server-checks'), "{$check}.job");

            if (! is_string($jobClass) || ! class_exists($jobClass)) {
                Log::warning('Attempted to queue unknown server check', ['check' => $check]);

                continue;
            }

            $jobs[] = new $jobClass($server->id, $runId);
        }

        $jobs[] = new FinalizeServerCheckRun($server->id, $runId, $checks);

        return $jobs;
    }
}
