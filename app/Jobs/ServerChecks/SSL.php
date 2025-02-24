<?php

namespace App\Jobs\ServerChecks;

use App\Models\CheckHistory;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SSL implements ShouldQueue
{
    use Batchable, Queueable;

    protected $check_settings;

    /**
     * SSL Job
     *
     * This job handles SSL checks for a given server.
     *
     * @param  object  $server  The server object containing server details and settings.
     * @param  mixed  $curl_result  The result from a cURL request.
     * @param  int  $batch_id  The ID of the batch this job belongs to.
     * @return void
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
        // 1. Checks if SSL checks are enabled in the settings.
        if (! $this->check_settings->SSL->enabled) {
            // 2. Logs a debug message if SSL checks are not enabled and exits.
            Log::debug('SSL check is not enabled for server.');

            return;
        }

        // 3. Retrieves SSL certificate information from the curl result.
        $certinfo = $this->curl_result['certinfo'];
        // 4. Logs a warning if no SSL certificate is found and exits.
        if (empty($certinfo)) {
            CheckHistory::create([
                'server_id' => $this->server->id,
                'batch_id' => $this->batch_id,
                'name' => 'SSL_certificate_valid',
                'status' => 'danger',
                'message' => 'No SSL certificate found.',
                'check_settings' => json_encode($this->check_settings->SSL->SSL_certificate_valid),
            ]);

            CheckHistory::create([
                'server_id' => $this->server->id,
                'batch_id' => $this->batch_id,
                'name' => 'SSL_expiration',
                'status' => 'fail',
                'message' => 'Could not test because no SSL certificate found.',
                'check_settings' => json_encode($this->check_settings->SSL->SSL_expiration),
            ]);
            Log::warning('No SSL certificate found.', ['info' => $certinfo]);

            return;
        }

        // 5. Parses the SSL certificate information.
        $certinfo = openssl_x509_parse($certinfo[0]['Cert']);
        $cert_expiration_date = $certinfo['validTo_time_t'];
        $expiration_time = $this->curl_result['certinfo'][0]['Expire date'];

        // 6. Logs the parsed SSL certificate information for debugging purposes.
        Log::debug('SSL Cert info', ['certinfo' => $certinfo]);

        // 7. Checks the validity of the SSL certificate.
        $this->checkSSLCertificateValidity($cert_expiration_date, $expiration_time);
        // 8. Checks the expiration date of the SSL certificate.
        $this->checkSSLExpiration($cert_expiration_date, $expiration_time);

        // 9. Logs a debug message indicating the completion of the SSL check.
        Log::debug('Completed SSL check for server.');
    }

    /**
     * Check SSL certificate validity.
     *
     * This method checks if the SSL certificate is valid based on the provided expiration date.
     * It logs the validation process and stores the result in the CheckHistory.
     *
     * @param  int  $cert_expiration_date  The expiration date of the SSL certificate as a Unix timestamp.
     * @param  int  $expiration_time  The time remaining until the SSL certificate expires.
     */
    protected function checkSSLCertificateValidity($cert_expiration_date, $expiration_time): void
    {
        if ($this->check_settings->SSL->SSL_certificate_valid->enabled !== true) {
            Log::debug('Checking for valid SSL certificate is not enabled.');

            return;
        }

        Log::debug('Validating SSL certificate.');
        $status = $cert_expiration_date > time() ? 'success' : 'error';
        $message = $status === 'success' ? 'SSL certificate is valid' : 'SSL certificate is not valid';

        CheckHistory::create([
            'server_id' => $this->server->id,
            'batch_id' => $this->batch_id,
            'name' => 'SSL_certificate_valid',
            'status' => $status,
            'message' => $message,
            'check_settings' => json_encode($this->check_settings->SSL->SSL_certificate_valid),
        ]);

        Log::log($status === 'success' ? 'info' : 'warning', $message, ['expiration_time' => $expiration_time]);
    }

    /**
     * Check SSL expiration.
     *
     * This method checks if the SSL certificate is about to expire based on the
     * provided expiration date and the configured threshold for days to expiration.
     * It logs the status and creates a record in the check history.
     *
     * @param  int  $cert_expiration_date  The expiration date of the SSL certificate as a Unix timestamp.
     * @param  int  $expiration_time  The time when the expiration check was performed as a Unix timestamp.
     */
    protected function checkSSLExpiration($cert_expiration_date, $expiration_time): void
    {
        if ($this->check_settings->SSL->SSL_expiration->enabled !== true) {
            Log::debug('Checking for SSL expiration is not enabled.');

            return;
        }

        Log::debug('Checking for SSL expiration.');
        $expiration_days = round(($cert_expiration_date - time()) / 86400);

        if ($expiration_days < 0) {
            $status = 'danger';
            $message = 'SSL certificate expired '.abs($expiration_days).' days ago.';
            CheckHistory::create([
                'server_id' => $this->server->id,
                'batch_id' => $this->batch_id,
                'name' => 'SSL_expiration',
                'status' => 'danger',
                'message' => $message,
                'check_settings' => json_encode($this->check_settings->SSL->SSL_expiration),
            ]);
            Log::log('warning', $message, ['expiration_time' => $expiration_time]);
        }

        $days_to_expiration = $this->check_settings->SSL->SSL_expiration->input->days;
        $status = $expiration_days < $days_to_expiration ? 'warning' : 'success';
        $message = $status === 'warning'
            ? 'SSL certificate is about to expire in '.$expiration_days.' days.'
            : 'SSL certificate won\'t expire soon, it will expire in '.$expiration_days.' days.';

        CheckHistory::create([
            'server_id' => $this->server->id,
            'batch_id' => $this->batch_id,
            'name' => 'SSL_expiration',
            'status' => $status,
            'message' => $message,
            'check_settings' => json_encode($this->check_settings->SSL->SSL_expiration),
        ]);

        Log::log($status === 'warning' ? 'warning' : 'info', $message, ['expiration_time' => $expiration_time]);
    }
}
