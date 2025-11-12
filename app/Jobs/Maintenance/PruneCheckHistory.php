<?php

namespace App\Jobs\Maintenance;

use App\Enums\QueueName;
use App\Models\CheckHistory;
use App\Settings\GeneralSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class PruneCheckHistory implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue(QueueName::MAINTENANCE->value);
    }

    public function handle(GeneralSettings $settings): void
    {
        $days = max(1, (int) ($settings->check_history_retention_days ?? 7));
        $cutoff = now()->subDays($days);

        CheckHistory::where('created_at', '<', $cutoff)->delete();
    }
}
