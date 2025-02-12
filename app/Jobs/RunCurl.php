<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batchable;
use Throwable;

use App\Jobs\ServerChecks\StatusCode;
use App\Jobs\ServerChecks\SSL;

class RunCurl implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $href)
    {
        $this->onQueue('ServerTest');
        $this->href = $href;
    }
    /**
     * Execute the job.
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
        $this->href = preg_replace('/(.*)(%cachebuster%)/', '$0' . time(), $this->href);
    
        curl_setopt($ch, CURLOPT_URL, $this->href);
    
        $result['exec'] = curl_exec($ch);
        $result['info'] = curl_getinfo($ch);
    
        curl_close($ch);
        Log::debug('Hello there', $result['info']);
        
        $this->prependToChain(new SSL($result['info']));
        $this->prependToChain(new StatusCode($result['info']));
    }
}
