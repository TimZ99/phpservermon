<?php

use App\Jobs\ServerChecks\Concerns\InteractsWithCertificate;
use App\Jobs\ServerChecks\ContentRegex;
use App\Jobs\ServerChecks\Headers;
use App\Jobs\ServerChecks\Latency;
use App\Jobs\ServerChecks\SSLActive;
use App\Jobs\ServerChecks\SSLCertificateValid;
use App\Jobs\ServerChecks\SSLExpiration;
use App\Jobs\ServerChecks\StatusCode;
use App\Models\CheckHistory;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function serverPayload(Server $server, array $overrides = []): array
{
    $base = [
        'server' => [
            'id' => $server->id,
            'name' => $server->name,
            'ip' => $server->ip,
        ],
        'curl' => [
            'info' => [
                'http_code' => 200,
                'url' => $server->ip,
                'certinfo' => [],
            ],
            'headers' => [],
            'body' => '',
            'latency_ms' => 100,
        ],
    ];

    return array_replace_recursive($base, $overrides);
}

function invokePerform(object $job, array $payload, array $settings = []): void
{
    $method = new \ReflectionMethod($job, 'perform');
    $method->setAccessible(true);
    $method->invoke($job, $payload, $settings);
}

it('evaluates status codes for all ranges', function () {
    $server = Server::factory()->create(['ip' => 'https://example.com']);
    $job = new StatusCode($server->id, (string) Str::uuid());

    invokePerform($job, serverPayload($server, ['curl' => ['info' => ['http_code' => 0]]]), ['enabled' => true]);
    expect(CheckHistory::where('message', 'No response from server')->exists())->toBeTrue();

    invokePerform($job, serverPayload($server, ['curl' => ['info' => ['http_code' => 200]]]), ['enabled' => true]);
    expect(CheckHistory::where('message', 'Status code OK (200)')->exists())->toBeTrue();

    invokePerform($job, serverPayload($server, ['curl' => ['info' => ['http_code' => 404]]]), ['enabled' => true]);
    expect(CheckHistory::where('message', 'Client error (404)')->exists())->toBeTrue();

    invokePerform($job, serverPayload($server, ['curl' => ['info' => ['http_code' => 503]]]), ['enabled' => true]);
    expect(CheckHistory::where('message', 'Server error (503)')->exists())->toBeTrue();
});

it('evaluates content regex patterns', function () {
    $server = Server::factory()->create(['ip' => 'https://content.test']);
    $job = new ContentRegex($server->id, (string) Str::uuid());
    $payload = serverPayload($server, ['curl' => ['body' => 'Hello World']]);

    invokePerform($job, $payload, ['input' => ['pattern' => '']]);
    expect(CheckHistory::where('name', 'ContentRegex')->where('status', 'warning')->exists())->toBeTrue();

    invokePerform($job, $payload, ['input' => ['pattern' => '/World/']]);
    expect(CheckHistory::where('name', 'ContentRegex')->where('status', 'success')->exists())->toBeTrue();

    invokePerform($job, $payload, ['input' => ['pattern' => '/Foo/']]);
    expect(CheckHistory::where('name', 'ContentRegex')->where('status', 'fail')->exists())->toBeTrue();
});

it('validates required headers with literal and regex expectations', function () {
    $server = Server::factory()->create(['ip' => 'https://headers.test']);
    $job = new Headers($server->id, (string) Str::uuid());

    invokePerform($job, serverPayload($server, ['curl' => ['headers' => []]]), ['input' => ['required' => []]]);
    expect(CheckHistory::where('name', 'Headers')->where('message', 'No headers were required.')->exists())->toBeTrue();

    $payload = serverPayload($server, [
        'curl' => [
            'headers' => [
                'x-token' => 'abc123',
                'x-match' => 'foobar',
                'x-optional' => 'something',
            ],
        ],
    ]);

    invokePerform($job, $payload, ['input' => ['required' => ['X-Optional' => null]]]);
    expect(CheckHistory::where('name', 'Headers')->where('message', 'All required headers are present.')->exists())->toBeTrue();

    invokePerform($job, $payload, ['input' => ['required' => ['X-Token' => 'abc123', 'X-Match' => '/^foo/']]]);
    expect(CheckHistory::where('name', 'Headers')->where('status', 'success')->exists())->toBeTrue();

    invokePerform($job, $payload, ['input' => ['required' => ['Missing' => null, 'X-Match' => '/nope/']]]);
    expect(CheckHistory::where('name', 'Headers')->where('status', 'fail')->exists())->toBeTrue();

    invokePerform($job, $payload, ['input' => ['required' => ['X-Token' => 'expected']]]);
    expect(CheckHistory::where('name', 'Headers')->where('message', "X-Token value 'abc123' does not match expected 'expected'")->exists())->toBeTrue();
});

it('evaluates latency thresholds and missing values', function () {
    $server = Server::factory()->create(['ip' => 'https://latency.test']);
    $job = new Latency($server->id, (string) Str::uuid());

    invokePerform($job, serverPayload($server, ['curl' => ['latency_ms' => null]]), Latency::defaults());
    expect(CheckHistory::where('name', 'Latency')->where('status', 'warning')->where('message', 'Latency was not reported by cURL.')->exists())->toBeTrue();

    invokePerform($job, serverPayload($server, ['curl' => ['latency_ms' => 2000]]), Latency::defaults());
    expect(CheckHistory::where('name', 'Latency')->where('status', 'fail')->where('message', 'Latency 2000ms exceeds failure threshold (1500ms).')->exists())->toBeTrue();

    invokePerform($job, serverPayload($server, ['curl' => ['latency_ms' => 800]]), Latency::defaults());
    expect(CheckHistory::where('name', 'Latency')->where('status', 'warning')->where('message', 'Latency 800ms exceeds warning threshold (600ms).')->exists())->toBeTrue();

    invokePerform($job, serverPayload($server, ['curl' => ['latency_ms' => 100]]), Latency::defaults());
    expect(CheckHistory::where('name', 'Latency')->where('status', 'success')->where('message', 'Latency 100ms is within acceptable limits.')->exists())->toBeTrue();
});

it('parses certificate payloads using the InteractsWithCertificate trait', function () {
    $cert = file_get_contents(base_path('tests/Fixtures/test-cert.pem'));

    $stub = new class
    {
        use InteractsWithCertificate;

        public function parse(array $payload): ?array
        {
            return $this->extractCertificate($payload);
        }
    };

    $payload = [
        'curl' => [
            'info' => [
                'certinfo' => [
                    ['Cert' => $cert],
                ],
            ],
        ],
    ];

    $parsed = $stub->parse($payload);

    expect($parsed)->not->toBeNull()
        ->and($parsed['subject']['CN'])->toBe('example.test');
});

it('returns null when certificate payload cannot be parsed', function () {
    $stub = new class
    {
        use InteractsWithCertificate;

        public function parse(array $payload): ?array
        {
            return $this->extractCertificate($payload);
        }
    };

    $payload = [
        'curl' => [
            'info' => [
                'certinfo' => [
                    ['Cert' => 'invalid'],
                ],
            ],
        ],
    ];

    expect($stub->parse($payload))->toBeNull();
});

it('detects when HTTPS is active', function () {
    $server = Server::factory()->create(['ip' => 'https://ssl.test']);
    $job = new SSLActive($server->id, (string) Str::uuid());

    invokePerform($job, serverPayload($server, ['curl' => ['info' => ['url' => 'https://ssl.test']]]), SSLActive::defaults());
    expect(CheckHistory::where('name', 'SSL_active')->where('status', 'success')->exists())->toBeTrue();

    invokePerform($job, serverPayload($server, ['curl' => ['info' => ['url' => 'http://ssl.test']]]), SSLActive::defaults());
    expect(CheckHistory::where('name', 'SSL_active')->where('status', 'fail')->exists())->toBeTrue();
});

class TestableCertificateValid extends SSLCertificateValid
{
    protected function extractCertificate(array $payload): ?array
    {
        return $payload['certificate'] ?? parent::extractCertificate($payload);
    }
}

class TestableSSLExpiration extends SSLExpiration
{
    protected function extractCertificate(array $payload): ?array
    {
        return $payload['certificate'] ?? parent::extractCertificate($payload);
    }
}

it('evaluates ssl certificate validity states', function () {
    $server = Server::factory()->create(['ip' => 'https://cert-valid.test']);
    $job = new TestableCertificateValid($server->id, (string) Str::uuid());

    invokePerform($job, serverPayload($server, ['certificate' => null]), SSLCertificateValid::defaults());
    expect(CheckHistory::where('name', 'SSL_certificate_valid')->where('message', 'No SSL certificate presented.')->exists())->toBeTrue();

    invokePerform($job, serverPayload($server, ['certificate' => ['validTo_time_t' => time() - 100]]), SSLCertificateValid::defaults());
    expect(CheckHistory::where('name', 'SSL_certificate_valid')->where('message', 'SSL certificate has expired.')->exists())->toBeTrue();

    invokePerform($job, serverPayload($server, ['certificate' => ['validTo_time_t' => time() + 3600]]), SSLCertificateValid::defaults());
    expect(CheckHistory::where('name', 'SSL_certificate_valid')->where('message', 'SSL certificate is currently valid.')->exists())->toBeTrue();
});

it('evaluates ssl expiration thresholds', function () {
    $server = Server::factory()->create(['ip' => 'https://cert-exp.test']);
    $job = new TestableSSLExpiration($server->id, (string) Str::uuid());

    invokePerform($job, serverPayload($server, ['certificate' => null]), SSLExpiration::defaults());
    expect(CheckHistory::where('name', 'SSL_expiration')->where('status', 'fail')->where('message', 'SSL expiration could not be verified because no certificate was found.')->exists())->toBeTrue();

    invokePerform($job, serverPayload($server, ['certificate' => ['validTo_time_t' => null]]), SSLExpiration::defaults());
    expect(CheckHistory::where('name', 'SSL_expiration')->where('message', 'SSL expiration date is missing.')->exists())->toBeTrue();

    invokePerform($job, serverPayload($server, ['certificate' => ['validTo_time_t' => time() - 3600]]), SSLExpiration::defaults());
    expect(CheckHistory::where('name', 'SSL_expiration')->where('message', 'LIKE', 'Certificate expired%')->exists())->toBeTrue();

    invokePerform($job, serverPayload($server, ['certificate' => ['validTo_time_t' => time() + 3600]]), ['input' => ['days' => 5]]);
    expect(CheckHistory::where('name', 'SSL_expiration')->where('status', 'warning')->exists())->toBeTrue();

    invokePerform($job, serverPayload($server, ['certificate' => ['validTo_time_t' => time() + 86400 * 10]]), ['input' => ['days' => 5]]);
    expect(CheckHistory::where('name', 'SSL_expiration')->where('status', 'success')->exists())->toBeTrue();
});
