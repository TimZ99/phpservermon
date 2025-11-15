<?php

namespace App\Jobs;

use App\Enums\QueueName;
use App\Models\CheckHistory;
use App\Models\Server;
use App\Notifications\ServerStatusChanged;
use App\Services\Notifications\NotificationPreferenceService;
use App\Services\ServerChecks\ServerCheckRunStore;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class FinalizeServerCheckRun implements ShouldQueue
{
    use Queueable;

    public const OVERALL_STATUS = '__OVERALLSTATUS__';

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

    public function handle(ServerCheckRunStore $store, NotificationPreferenceService $preferences): void
    {
        $results = CheckHistory::query()
            ->where('server_id', $this->serverId)
            ->where('run_curl_batch_id', $this->runId)
            ->where('name', '!=', self::OVERALL_STATUS)
            ->get();

        $overall = $this->determineStatus($results);
        $message = $this->buildSummaryMessage($results, $overall);

        CheckHistory::create([
            'server_id' => $this->serverId,
            'run_curl_batch_id' => $this->runId,
            'server_checks_batch_id' => $this->runId,
            'name' => self::OVERALL_STATUS,
            'status' => $overall,
            'message' => $message,
            'check_settings' => json_encode(['checks' => $this->checks]),
        ]);

        $server = Server::with('users')->find($this->serverId);
        $statusChanged = $this->updateServerState($server, $overall);
        $summary = $this->buildSummary($results);

        if ($server && $statusChanged) {
            $this->notifyUsers($server, $overall, $summary, $preferences);
        }

        $store->forget($this->runId);
    }

    protected function determineStatus(Collection $results): string
    {
        $statuses = $results->pluck('status')->map(function ($status) {
            return match ($status) {
                'fail', 'danger', 'error' => 'fail',
                'warning' => 'warning',
                default => 'success',
            };
        });

        if ($statuses->isEmpty()) {
            return 'success';
        }

        if ($statuses->contains('fail')) {
            return 'fail';
        }

        if ($statuses->contains('warning')) {
            return 'warning';
        }

        return 'success';
    }

    protected function buildSummaryMessage(Collection $results, string $overall): string
    {
        $total = $results->count();
        $failures = $results->where('status', 'fail')->count()
            + $results->whereIn('status', ['danger', 'error'])->count();
        $warnings = $results->where('status', 'warning')->count();

        if ($total === 0) {
            return sprintf('Overall status %s with no checks executed.', strtoupper($overall));
        }

        return sprintf(
            'Overall status %s after %d checks (%d fail, %d warning).',
            strtoupper($overall),
            $total,
            $failures,
            $warnings
        );
    }

    protected function updateServerState(?Server $server, string $overall): bool
    {
        if (! $server) {
            return false;
        }

        $attributes = [
            'last_check_run_id' => $this->runId,
            'last_checked_at' => now(),
        ];

        $changed = $server->overall_status !== $overall;
        if ($changed) {
            $attributes['overall_status'] = $overall;
            $attributes['overall_status_changed_at'] = now();
        }

        $server->forceFill($attributes)->save();

        return $changed;
    }

    /**
     * @return array<string, array<int, array{name: string, message: string}>>
     */
    protected function buildSummary(Collection $results): array
    {
        $normalized = $results->map(function ($result) {
            $status = match ($result->status) {
                'fail', 'danger', 'error' => 'fail',
                'warning' => 'warning',
                default => 'success',
            };

            return [
                'status' => $status,
                'name' => $result->name,
                'message' => $result->message,
            ];
        });

        return [
            'failures' => $normalized->where('status', 'fail')->map(fn ($item) => [
                'name' => $item['name'],
                'message' => $item['message'],
            ])->values()->toArray(),
            'warnings' => $normalized->where('status', 'warning')->map(fn ($item) => [
                'name' => $item['name'],
                'message' => $item['message'],
            ])->values()->toArray(),
        ];
    }

    protected function notifyUsers(Server $server, string $overall, array $summary, NotificationPreferenceService $preferences): void
    {
        foreach ($server->users as $user) {
            $channels = $preferences->channelsFor($user, $server, self::OVERALL_STATUS);

            if (empty($channels)) {
                continue;
            }

            Notification::sendNow($user, new ServerStatusChanged($server, $overall, $summary, $channels));
        }
    }
}
