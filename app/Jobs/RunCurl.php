<?php

namespace App\Jobs;

use App\Models\Server;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class RunCurl implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * Create a new job instance.
     *
     * @param  Server  $server  The server instance to be tested.
     * @param  array  $checks  An array of checks to be performed on the server.
     * @return void
     */
    public function __construct(protected Server $server, protected array $checks = [])
    {
        $this->onQueue('ServerTest');
        $this->server = $server;
        $this->checks = $checks;
    }

    /**
     * Handle the job to run a cURL request and dispatch server checks.
     */
    public function handle(): void
    {
        // 1 Updates the server IP with a cache buster.
        $this->server->ip = preg_replace('/^(.*)%cachebuster%/', '$0'.time(), $this->server->ip);

        // 2 Initializes a cURL session and sets various options.
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_CERTINFO, 1);
        curl_setopt($curl, CURLOPT_URL, $this->server->ip);

        // 3 Executes the cURL request and retrieves the result and info.
        $result = [
            'exec' => curl_exec($curl),
            'info' => curl_getinfo($curl),
        ];

        curl_close($curl);

        // 4 Logs the start of tests for the server.
        Log::debug('Start tests for server. First curl website.', ['server' => $this->server, 'result' => $result, 'batch_id' => $this->batch()->id]);
        $jobs = [];

        // 5 Decodes and filters the server check settings if checks are not already set.
        if (empty($this->checks)) {
            $decodedSettings = json_decode($this->server->check_settings, true);
            $this->checks = array_keys(array_filter($decodedSettings, fn ($settings) => $settings['enabled']));
        }

        // 6 Ensures the checks array contains unique values.
        $this->checks = array_unique($this->checks);

        // 7 Iterates over the checks, creating job instances for each valid check class.
        foreach ($this->checks as $check) {
            $checkClass = 'App\Jobs\ServerChecks\\'.$check;
            if (class_exists($checkClass)) {
                // class need the following properties: server, curl_result, run_curl_batch_id
                $jobs[] = new $checkClass($this->server, $result['info'], $this->batch()->id);
            } else {
                Log::warning('Server check class does not exist', ['checkClass' => $checkClass]);
            }
        }

        // 8 Logs a warning if no jobs were created and returns early.
        if (empty($jobs)) {
            Log::warning('No server checks were dispatched because no jobs were created.');

            return;
        }

        // 9 Dispatches a batch of server check jobs to the 'ServerTest' queue.
        Bus::batch($jobs)
            ->name('Tests for server '.$this->server->id)
            ->onQueue('ServerTest')
            ->dispatch();
    }
}
