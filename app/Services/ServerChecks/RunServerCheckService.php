<?php

namespace App\Services\ServerChecks;

use App\Models\Server;
use Illuminate\Support\Collection;

class RunServerCheckService
{
    public function __construct(private readonly ServerCheckOrchestrator $orchestrator) {}

    /**
     * @param  iterable<Server>  $servers
     * @param  array<string>  $limitToChecks
     * @return array<string, string>
     */
    public function handle(iterable $servers, bool $trackSession = true, array $limitToChecks = []): array
    {
        $collection = $servers instanceof Collection ? $servers : collect($servers);
        if ($collection->isEmpty()) {
            return [];
        }

        $runIds = $this->orchestrator->dispatch($collection, $limitToChecks);

        if ($trackSession) {
            foreach ($runIds as $serverId => $runId) {
                session(["server_run.{$serverId}" => $runId]);
            }
        }

        return $runIds;
    }
}
