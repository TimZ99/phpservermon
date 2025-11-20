<?php

namespace App\Jobs {

    use Tests\Unit\RunCurlFake;

    if (! function_exists(__NAMESPACE__.'\curl_init')) {
        function curl_init()
        {
            return RunCurlFake::$handle;
        }
    }

    if (! function_exists(__NAMESPACE__.'\curl_setopt')) {
        function curl_setopt($handle, $option, $value)
        {
            RunCurlFake::$options[$option] = $value;

            return true;
        }
    }

    if (! function_exists(__NAMESPACE__.'\curl_exec')) {
        function curl_exec()
        {
            return RunCurlFake::$execResponse;
        }
    }

    if (! function_exists(__NAMESPACE__.'\curl_getinfo')) {
        function curl_getinfo()
        {
            return RunCurlFake::$info;
        }
    }

    if (! function_exists(__NAMESPACE__.'\curl_error')) {
        function curl_error()
        {
            return RunCurlFake::$error;
        }
    }

    if (! function_exists(__NAMESPACE__.'\curl_close')) {
        function curl_close()
        {
            //
        }
    }
}

namespace Tests\Unit {

    use App\Jobs\RunCurl;
    use App\Models\Server;
    use App\Services\ServerChecks\ServerCheckRunStore;
    use Illuminate\Support\Facades\Log;
    use Mockery;

    class RunCurlFake
    {
        public static string $handle = 'curl-handle';

        public static mixed $execResponse = '';

        public static array $info = [];

        public static ?string $error = null;

        public static array $options = [];

        public static function reset(): void
        {
            self::$execResponse = '';
            self::$info = [];
            self::$error = null;
            self::$options = [];
        }
    }

    beforeEach(function () {
        RunCurlFake::reset();
    });

    afterEach(function () {
        Mockery::close();
    });

    it('adds cachebuster to server ip when placeholder exists', function () {
        $server = Server::factory()->create(['ip' => 'https://example.com/%cachebuster%/status']);
        $job = new RunCurl($server->id, 'run', []);

        $method = new \ReflectionMethod($job, 'prepareServerForCurl');
        $method->setAccessible(true);

        $method->invoke($job, $server);

        expect($server->ip)->toMatch('#https://example\.com/\d+/status$#');
    });

    it('leaves server ip unchanged when no cachebuster placeholder', function () {
        $server = Server::factory()->create(['ip' => 'https://example.com/ping']);
        $job = new RunCurl($server->id, 'run', []);

        $method = new \ReflectionMethod($job, 'prepareServerForCurl');
        $method->setAccessible(true);

        $method->invoke($job, $server);

        expect($server->ip)->toBe('https://example.com/ping');
    });

    it('parses header names case-insensitively and trims values', function () {
        $job = new RunCurl('id', 'run', []);
        $method = new \ReflectionMethod($job, 'parseHeaders');
        $method->setAccessible(true);

        $headers = $method->invoke($job, "Content-Type: text/html\r\nX-Test:  value \r\n");

        expect($headers)->toBe([
            'content-type' => 'text/html',
            'x-test' => 'value',
        ]);
    });

    it('returns empty array when parsing empty headers', function () {
        $job = new RunCurl('id', 'run', []);
        $method = new \ReflectionMethod($job, 'parseHeaders');
        $method->setAccessible(true);

        expect($method->invoke($job, null))->toBe([]);
    });

    it('stores curl payload with parsed headers and metadata', function () {
        $server = Server::factory()->create([
            'ip' => 'https://status.test/%cachebuster%/health',
            'port' => 8443,
            'check_settings' => ['Latency' => ['enabled' => true]],
        ]);

        $rawHeaders = "HTTP/1.1 200 OK\r\nX-Env: demo\r\n\r\n";
        $body = '{"ok":true}';
        RunCurlFake::$execResponse = $rawHeaders.$body;
        RunCurlFake::$info = ['header_size' => strlen($rawHeaders), 'total_time' => 0.25];
        RunCurlFake::$error = null;

        $store = Mockery::mock(ServerCheckRunStore::class);
        $store->shouldReceive('put')
            ->once()
            ->withArgs(function ($runId, $payload) use ($server, $rawHeaders, $body) {
                expect($runId)->toBe('run-xyz');
                expect($payload['server']['id'])->toBe($server->id);
                expect($payload['checks'])->toBe(['Latency']);
                expect($payload['curl']['raw_headers'])->toBe($rawHeaders);
                expect($payload['curl']['body'])->toBe($body);
                expect($payload['curl']['headers'])->toBe(['x-env' => 'demo']);
                expect($payload['curl']['latency_ms'])->toBe(250);

                return true;
            });

        Log::shouldReceive('debug')->once();

        $job = new RunCurl($server->id, 'run-xyz', ['Latency']);
        $job->handle($store);

        expect(RunCurlFake::$options[\CURLOPT_URL])->toStartWith('https://status.test/');
        expect(RunCurlFake::$options[\CURLOPT_PORT])->toBe(8443);
    });

    it('stores error information when curl fails', function () {
        $server = Server::factory()->create([
            'ip' => 'https://example.org/health',
            'port' => null,
            'check_settings' => [],
        ]);

        RunCurlFake::$execResponse = false;
        RunCurlFake::$info = [];
        RunCurlFake::$error = 'timeout';

        $store = Mockery::mock(ServerCheckRunStore::class);
        $store->shouldReceive('put')
            ->once()
            ->withArgs(function ($runId, $payload) use ($server) {
                expect($runId)->toBe('run-error');
                expect($payload['server']['id'])->toBe($server->id);
                expect($payload['curl']['body'])->toBeNull();
                expect($payload['curl']['raw_headers'])->toBe('');
                expect($payload['curl']['headers'])->toBe([]);
                expect($payload['curl']['error'])->toBe('timeout');

                return true;
            });

        Log::shouldReceive('debug')->once();

        $job = new RunCurl($server->id, 'run-error', []);
        $job->handle($store);

        expect(RunCurlFake::$options)->not->toHaveKey(\CURLOPT_PORT);
    });

}
