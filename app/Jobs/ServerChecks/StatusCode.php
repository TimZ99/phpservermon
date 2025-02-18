<?php

namespace App\Jobs\ServerChecks;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Batchable;
use App\Models\CheckHistory;

class StatusCode implements ShouldQueue
{
    use Batchable, Queueable;

    protected $check_settings;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $server, protected $curl_result, protected $batch_id)
    {
        $this->onQueue('ServerTest');
        $this->curl_result = $curl_result;
        $this->check_settings = json_decode($this->server->check_settings);
        $this->batch_id = $batch_id;
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
        $status = 'error';

        switch ($code) {
            case 0:
                Log::warning('TIMEOUT ERROR: no response from server', [$this->curl_result]);
                $message = 'TIMEOUT ERROR: no response from server';
                break;
            case 200:
                Log::info('Status code is OK', [$this->curl_result]);
                $status = 'success';
                $message = 'Status code is OK (' . $code . ')';
                break;
            case 301:
                Log::info('Resource moved permanently', [$this->curl_result]);
                $status = 'warning';
                $message = 'Resource moved permanently (' . $code . ')';
                break;
            case 404:
                Log::error('Resource not found', [$this->curl_result]);
                $message = 'Resource not found (' . $code . ')';
                break;
            case 500:
                Log::error('Internal server error', [$this->curl_result]);
                $message = 'Internal server error (' . $code . ')';
                break;
            default:
                Log::info('Unhandled status code: ' . (string) $code, [$this->curl_result]);
                $message = 'Unhandled status code: ' . (string) $code;
                break;
            }

        CheckHistory::create([
            'server_id' => $this->server->id,
            'batch_id' => $this->batch_id,
            'name' => 'SSL_certificate_valid',
            'status' => $status,
            'message' => $message,
            'check_settings' => json_encode($this->check_settings->status_code)
        ]);
    }
}
