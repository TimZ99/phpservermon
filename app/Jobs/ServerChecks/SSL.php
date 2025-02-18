<?php

namespace App\Jobs\ServerChecks;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Batchable;
use App\Models\CheckHistory;

class SSL implements ShouldQueue
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
                CheckHistory::create([
                    'server_id' => $this->server->id,
                    //'batch_id' => $this->batch_id,
                    'name' => 'SSL_certificate_valid',
                    'status' => 'success',
                    'message' => 'SSL certificate is valid',
                    'check_settings' => json_encode($this->check_settings->SSL->SSL_expiration)]);
                Log::info('SSL certificate is valid.', ['expiration_time' => $expiration_time]);
            } else {
                CheckHistory::create([
                    'server_id' => $this->server->id,
                    'batch_id' => $this->batch_id,
                    'name' => 'SSL_certificate_valid',
                    'status' => 'error',
                    'message' => 'SSL certificate is not valid',
                    'check_settings' => json_encode($this->check_settings->SSL->SSL_expiration)
                ]);
                Log::warning('SSL certificate is not valid.', ['expiration_time' => $expiration_time]);
            }
        }
        if ($this->check_settings->SSL->SSL_expiration->enabled !== true) {
            Log::debug('Checking for SSL expiration is not enabled.');
        } else {
            Log::debug('Checking for SSL expiration.');
            $expiration_days = round(($cert_expiration_date - time()) / 86400);
            if ($expiration_days < $this->check_settings->SSL->SSL_expiration->input->days) {
                CheckHistory::create([
                    'server_id' => $this->server->id,
                    'batch_id' => $this->batch_id,
                    'name' => 'SSL_certificate_valid',
                    'status' => 'warning',
                    'message' => 'SSL certificate is about to expire in ' . $expiration_days . ' days.',
                    'check_settings' => json_encode($this->check_settings->SSL->SSL_certificate_valid)
                ]);
                Log::warning('SSL certificate is about to expire in ' . $expiration_days . ' days.', ['expiration_time' => $expiration_time]);
            } else {
                CheckHistory::create([
                    'server_id' => $this->server->id,
                    'batch_id' => $this->batch_id,
                    'name' => 'SSL_certificate_valid',
                    'status' => 'success',
                    'message' => 'SSL certificate won\'t expire soon, it will expire in ' . $expiration_days . ' days.',
                    'check_settings' => json_encode($this->check_settings->SSL->SSL_certificate_valid)
                ]);
                $days_to_expiration = $this->check_settings->SSL->SSL_expiration->input->days;
                Log::info('SSL certificate will not expire in ' . $days_to_expiration . ' days. (' . $expiration_days . ')', ['expiration_time' => $expiration_time]);
            }
            
        }

        Log::debug('Completed SSL check for server.');
    }
}
