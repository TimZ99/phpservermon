<?php

namespace App\Jobs\ServerChecks;

use App\Models\CheckHistory;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class Template implements ShouldQueue
{
    use Batchable, Queueable;

    protected $check_settings;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $server, protected $curl_result, protected $run_curl_batch_id)
    {
        $this->onQueue('ServerTest');
        $this->curl_result = $curl_result;
        $this->check_settings = json_decode($this->server->check_settings);
        $this->run_curl_batch_id = $run_curl_batch_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! isset($this->check_settings->Template) || ! $this->check_settings->Template->enabled) {
            logger()->debug('Template check is not enabled for server.');

            return;
        }

        logger()->error('Template check ran!');
        // success / warning / danger
        $status = 'danger';
        // string
        $message = 'Template check ran! This will be replaced with actual check text.';

        CheckHistory::create([
            'server_id' => $this->server->id,
            'run_curl_batch_id' => $this->run_curl_batch_id,
            'server_checks_batch_id' => $this->batch()->id,
            'name' => 'Template',
            'status' => $status,
            'message' => $message,
            'check_settings' => isset($this->check_settings->Template) ? json_encode($this->check_settings->Template) : null,
        ]);
    }
}
