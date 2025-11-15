<?php

namespace App\Jobs\Heartbeat;

use App\Enums\QueueName;
use App\Services\Queue\QueueHeartbeatService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class CurlWorkerHeartbeat implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue(QueueName::CURL->value);
    }

    public function handle(QueueHeartbeatService $heartbeat): void
    {
        $heartbeat->record(QueueName::CURL);
    }
}
