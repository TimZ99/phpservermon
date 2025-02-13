<?php

namespace App\Jobs\ServerChecks;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Batchable;

class StatusCode implements ShouldQueue
{
    use Batchable, Queueable;

    protected $check_settings;
    protected $id;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $server,protected $curl_result)
    {
        $this->onQueue('ServerTest');
        $this->curl_result = $curl_result;
        $this->check_settings = json_decode($this->server->check_settings);
        $this->id = $server->id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if(!$this->check_settings->status_code->enabled) {
            Log::debug('Status code check is not enabled for server.');
            return;
        }

        $code = $this->curl_result['http_code'];

        switch ($code) {
            case 0:
                Log::warning('TIMEOUT ERROR: no response from server', [$this->curl_result]);
                break;
            case 200:
                Log::info('Status code is OK', [$this->curl_result]);
                break;
            case 301:
                Log::info('Resource moved permanently', [$this->curl_result]);
                break;
            case 404:
                Log::error('Resource not found', [$this->curl_result]);
                break;
            case 500:
                Log::error('Internal server error', [$this->curl_result]);
                break;
            default:
                Log::info('Unhandled status code: ' . (string) $code, [$this->curl_result]);
                break;
        }
    }
}
