<?php

namespace App\Services\Queue;

use App\Enums\QueueName;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;

class QueueHeartbeatService
{
    private const CACHE_KEY_PREFIX = 'queues:heartbeat:';

    public function record(QueueName|string $queue, ?CarbonInterface $at = null): void
    {
        $key = $this->key($queue);
        $timestamp = ($at ?? now())->timestamp;
        Cache::put($key, $timestamp, now()->addMinutes(10));
    }

    public function lastBeat(QueueName|string $queue): ?CarbonInterface
    {
        $timestamp = Cache::get($this->key($queue));
        if ($timestamp === null) {
            return null;
        }

        return now()->setTimestamp((int) $timestamp);
    }

    public function isAlive(QueueName|string $queue, int $thresholdSeconds = 630): bool
    {
        $lastBeat = $this->lastBeat($queue);

        return $lastBeat !== null && $lastBeat->greaterThanOrEqualTo(now()->subSeconds($thresholdSeconds));
    }

    private function key(QueueName|string $queue): string
    {
        $name = $queue instanceof QueueName ? $queue->value : (string) $queue;

        return self::CACHE_KEY_PREFIX.$name;
    }
}
