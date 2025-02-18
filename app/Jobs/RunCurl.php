<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batchable;
use App\Models\Server;

class RunCurl implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Server $server, protected array $checks)
    {
        $this->onQueue('ServerTest');
        $this->server = $server;
        $this->checks = $checks;
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->server->ip = preg_replace('/^(.*)%cachebuster%/', '$1' . time(), $this->server->ip);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_CERTINFO, 1);
        curl_setopt($curl, CURLOPT_URL, $this->server->ip);
    
        $result = [
            'exec' => curl_exec($curl),
            'info' => curl_getinfo($curl),
        ];
    
        curl_close($curl);

        Log::debug('Start tests for server. First curl website.', ['server' => $this->server, 'result' => $result, 'batch_id' => $this->batch()->id]);
        
        $jobs = [];
        
        foreach ($this->checks as $check) {
            $checkClass = 'App\Jobs\ServerChecks\\' . $check;
            if (class_exists($checkClass)) {
                $jobs[] = new $checkClass($this->server, $result['info'], $this->batch()->id);
            } else {
                Log::warning('Check class does not exist', [$checkClass]);
            }
        }

        Bus::batch($jobs)
            ->name('Tests for server ' . $this->server->id)
            ->onQueue('ServerTest')
            ->dispatch();
    }
}
