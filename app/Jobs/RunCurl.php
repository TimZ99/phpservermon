<?php

namespace App\Jobs;

use App\Enums\QueueName;
use App\Models\Server;
use App\Services\ServerChecks\ServerCheckRunStore;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class RunCurl implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @param  array<string>  $checks
     */
    public function __construct(
        protected string $serverId,
        protected string $runId,
        protected array $checks = []
    ) {
        $this->onQueue(QueueName::CURL->value);
    }

    public function handle(ServerCheckRunStore $store): void
    {
        $server = Server::findOrFail($this->serverId);
        $this->prepareServerForCurl($server);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_CERTINFO, 1);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_URL, $server->ip);

        if (! empty($server->port)) {
            curl_setopt($curl, CURLOPT_PORT, $server->port);
        }

        $startedAt = now();
        $rawResponse = curl_exec($curl);
        $info = curl_getinfo($curl) ?: [];
        $error = curl_error($curl) ?: null;
        curl_close($curl);

        $headerSize = (int) Arr::get($info, 'header_size', 0);
        $rawHeaders = $headerSize > 0 && is_string($rawResponse)
            ? substr($rawResponse, 0, $headerSize)
            : '';

        $body = is_string($rawResponse) ? substr($rawResponse, $headerSize) : null;

        $headers = $this->parseHeaders($rawHeaders);
        $payload = [
            'server' => [
                'id' => $server->id,
                'name' => $server->name,
                'ip' => $server->ip,
                'port' => $server->port,
            ],
            'checks' => $this->checks,
            'check_settings' => $server->check_settings ?? [],
            'curl' => [
                'info' => $info,
                'body' => $body,
                'headers' => $headers,
                'raw_headers' => $rawHeaders,
                'error' => $error,
                'latency_ms' => isset($info['total_time']) ? (int) round($info['total_time'] * 1000) : null,
            ],
            'started_at' => $startedAt->toIso8601String(),
        ];

        $store->put($this->runId, $payload);
        Log::debug('Stored curl payload for server checks', ['run_id' => $this->runId, 'server_id' => $server->id]);
    }

    protected function prepareServerForCurl(Server $server): void
    {
        $server->ip = preg_replace('/^(.*)%cachebuster%/', '$0'.time(), $server->ip ?? '') ?? $server->ip;
    }

    /**
     * @return array<string, string>
     */
    protected function parseHeaders(?string $raw): array
    {
        if (empty($raw)) {
            return [];
        }

        $lines = preg_split("/(\r?\n)/", trim($raw)) ?: [];
        $headers = [];

        foreach ($lines as $line) {
            if (str_contains($line, ':')) {
                [$name, $value] = explode(':', $line, 2);
                $headers[trim(strtolower($name))] = trim($value);
            }
        }

        return $headers;
    }
}
