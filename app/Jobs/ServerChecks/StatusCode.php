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
        $code = $this->result['http_code'];

        if ($code === 0) {
            // somehow we dont have a proper response.
            Log::warning('TIMEOUT ERROR: no response from server', ['exec' => $this->result]);
            return;
        }

        if ($code === 200) {
            Log::info('Status code is OK ('. (string) $code .')', ['exec' => $this->result]);
            return;
        } 
        Log::warning('Unexpected status code ('. (string) $code .')', ['exec' => $this->result]);
    }
}
