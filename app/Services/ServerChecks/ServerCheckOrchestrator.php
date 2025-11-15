<?php

namespace App\Services\ServerChecks;

use App\Enums\QueueName;
use App\Models\Server;
use App\Services\Queue\QueueHeartbeatService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;

class ServerCheckOrchestrator
{
    public function __construct(
        private readonly QueueHeartbeatService $heartbeat,
        private readonly ServerCheckChainBuilder $chainBuilder
    ) {}

    /**
     * @param  iterable<Server>  $servers
     * @param  array<string>  $limitToChecks
     * @return array<string, string> Server ID => run ID
     */
    public function dispatch(iterable $servers, array $limitToChecks = []): array
    {
        $queueAlive = $this->heartbeat->isAlive(QueueName::CURL);
        $connection = $queueAlive ? null : 'sync';
        $runIds = [];

        foreach ($servers as $server) {
            $runId = (string) Str::uuid();
            $jobs = $this->chainBuilder->build($server, $runId, $limitToChecks);
            $pending = Bus::chain($jobs)
                ->onQueue(QueueName::CURL->value);

            if ($connection !== null) {
                $pending->onConnection($connection);
            }

            $pending->dispatch();
            $runIds[$server->id] = $runId;
        }

        return $runIds;
    }
}
