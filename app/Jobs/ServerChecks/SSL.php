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
    public function __construct(protected $check_settings, protected $curl_result)
    {
        $this->onQueue('ServerTest');
        $this->check_settings = $check_settings;
        $this->curl_result = $curl_result;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if(!$this->check_settings->SSL->enabled) {
            Log::debug('SSL check is not enabled for server.');
            return;
        }
        $certinfo = $this->curl_result['certinfo'];
        if (empty($certinfo)) {
            Log::warning('No SSL certificate found.', ['info' => $certinfo]);
            return;
        }
        
        $certinfo = openssl_x509_parse($certinfo[0]['Cert']);
        $cert_expiration_date = $certinfo['validTo_time_t'];
        $expiration_time = $this->curl_result['certinfo'][0]['Expire date'];
        
        Log::debug('SSL Cert info', ['certinfo' => $certinfo]);

        if ($this->check_settings->SSL->SSL_certificate_valid->enabled !== true) {
            Log::debug('Checking for valid SSL certificate is not enabled.');
        } else {
            Log::debug('Validating SSL certificate.');
            // Check if the SSL certificate is still valid
            if ($cert_expiration_date > time()) {
                Log::info('SSL certificate is valid.', ['expiration_time' => $expiration_time]);
            } else {
                Log::warning('SSL certificate is not valid.', ['expiration_time' => $expiration_time]);
            }
        }
        if ($this->check_settings->SSL->SSL_expiration->enabled !== true) {
            Log::debug('Checking for SSL expiration is not enabled.');
        } else {
            Log::debug('Checking for SSL expiration.');
            $expiration_days = round(($cert_expiration_date - time()) / 86400);
            if ($expiration_days < $this->check_settings->SSL->SSL_expiration->input->days) {
                Log::warning('SSL certificate will expire in ' . (string) $expiration_days . ' days.', ['expiration_time' => $expiration_time]);
            } else {
                $days_to_expiration = $this->check_settings->SSL->SSL_expiration->input->days;
                Log::info('SSL certificate will not expire in ' . $days_to_expiration . ' days. (' . $expiration_days . ')', ['expiration_time' => $expiration_time]);
            }
            
        }

        Log::debug('Completed SSL check for server.');
    }
}
