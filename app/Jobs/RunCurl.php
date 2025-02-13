<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batchable;
use App\Models\Server;
use Throwable;

use App\Jobs\ServerChecks\StatusCode;
use App\Jobs\ServerChecks\SSL;

class RunCurl implements ShouldQueue
{
    use Batchable, Queueable;

    protected $check_settings;
    /**
     * Create a new job instance.
     */
    public function __construct(protected Server $server)
    {
        $this->onQueue('ServerTest');
        $this->server = $server;
        $this->check_settings = json_decode($this->server->check_settings);
    }
    /**
     * Execute the job.x
     */
    public function handle(): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_CERTINFO, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, '');
        $this->server->ip = preg_replace('/(.*)(%cachebuster%)/', '$0' . time(), $this->server->ip);
    
        curl_setopt($ch, CURLOPT_URL, $this->server->ip);
    
        $result['exec'] = curl_exec($ch);
        $result['info'] = curl_getinfo($ch);
    
        curl_close($ch);

        Log::debug('Start tests for server. First curl website.', ['server' => $this->server, 'result' => $result]);
        
        $jobs = [
            new SSL($this->check_settings, $result['info']),
            new StatusCode($this->check_settings, $result['info'])
        ];
        
        
        
        Bus::batch($jobs)->name('Tests for server ' . $this->server->id)
            ->catch(function (Throwable $e) {
                Log::error('Error in RunCurl', ['error' => $e]);
            })
            ->onQueue('ServerTest')
            ->dispatch();
    }
}
