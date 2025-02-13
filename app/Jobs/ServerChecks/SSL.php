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
        $certinfo = $this->result['certinfo'];
        if (!empty($certinfo)) {
            $certinfo = openssl_x509_parse($certinfo[0]['Cert']);
            $cert_expiration_date = $certinfo['validTo_time_t'];
            $expiration_time = $this->result['certinfo'][0]['Expire date'];
           
            Log::debug('SSL Cert info', ['certinfo' => $certinfo]);

            // Check if the SSL certificate is still valid
            $cert_expiration_date > time() 
                ? Log::info('SSL certificate is valid.', ['expiration_time' => $expiration_time])
                : Log::warning('SSL certificate is not valid.', ['expiration_time' => $expiration_time]);
            
            return;
        }
        Log::warning('No SSL certificate found.', ['info' => $certinfo]);
        
    }
}
