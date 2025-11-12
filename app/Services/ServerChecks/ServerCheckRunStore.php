<?php

namespace App\Services\ServerChecks;

use Illuminate\Support\Facades\Cache;

class ServerCheckRunStore
{
    private const CACHE_KEY_PREFIX = 'server-check-run:';

    public function put(string $runId, array $payload, int $ttlSeconds = 600): void
    {
        Cache::put($this->key($runId), $payload, now()->addSeconds($ttlSeconds));
    }

    public function get(string $runId): array
    {
        return Cache::get($this->key($runId), []);
    }

    public function forget(string $runId): void
    {
        Cache::forget($this->key($runId));
    }

    private function key(string $runId): string
    {
        return self::CACHE_KEY_PREFIX.$runId;
    }
}
