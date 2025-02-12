<?php

namespace App\Jobs\ServerChecks;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Batchable;

class StatusCode implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $result)
    {
        $this->onQueue('ServerTest');
        $this->result = $result;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->result['http_code'] === 200) {
            Log::info('Status code is OK.', ['result' => $this->result]);
        } else {
            Log::warning('Unexpected status code.', ['result' => $this->result]);
        }
    }
}
