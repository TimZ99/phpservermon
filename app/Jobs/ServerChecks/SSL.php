<?php

namespace App\Jobs\ServerChecks;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Batchable;

class SSL implements ShouldQueue
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
        Log::debug('SSL test', ['result' => $this->result]);
    }
}
