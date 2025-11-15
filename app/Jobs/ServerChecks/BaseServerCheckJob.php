<?php

namespace App\Jobs\ServerChecks;

use App\Enums\QueueName;
use App\Models\CheckHistory;
use App\Services\ServerChecks\ServerCheckRunStore;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

abstract class BaseServerCheckJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $serverId,
        protected string $runId
    ) {
        $this->onQueue(QueueName::CURL->value);
    }

    public function handle(ServerCheckRunStore $store): void
    {
        $payload = $store->get($this->runId);

        if (empty($payload)) {
            logger()->warning('Missing payload for server check', ['run_id' => $this->runId, 'check' => $this->checkName()]);

            return;
        }

        $settings = $payload['check_settings'][$this->checkName()] ?? [];
        if (($settings['enabled'] ?? false) !== true) {
            logger()->debug('Server check disabled', ['check' => $this->checkName(), 'run_id' => $this->runId]);

            return;
        }

        $this->perform($payload, $settings);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $settings
     */
    abstract protected function perform(array $payload, array $settings): void;

    abstract protected function checkName(): string;

    public static function defaults(): array
    {
        return ['enabled' => false];
    }

    /**
     * @param  array<string, mixed>|null  $settings
     */
    protected function record(string $name, string $status, string $message, ?array $settings = null): void
    {
        CheckHistory::create([
            'server_id' => $this->serverId,
            'run_curl_batch_id' => $this->runId,
            'server_checks_batch_id' => $this->runId,
            'name' => $name,
            'status' => $status,
            'message' => $message,
            'check_settings' => $settings !== null ? json_encode($settings) : null,
        ]);
    }

    protected function normalizeStatus(string $status): string
    {
        return match (strtolower($status)) {
            'danger', 'error', 'fail', 'failed' => 'fail',
            'warning' => 'warning',
            default => 'success',
        };
    }
}
