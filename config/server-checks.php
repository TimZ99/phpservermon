<?php

return [
    'StatusCode' => [
        'job' => App\Jobs\ServerChecks\StatusCode::class,
        'description' => 'Ensures HTTP status codes stay within the 200–399 range.',
    ],
    'SSL_active' => [
        'job' => App\Jobs\ServerChecks\SSLActive::class,
        'description' => 'Verifies that HTTPS is active for the monitored endpoint.',
    ],
    'SSL_certificate_valid' => [
        'job' => App\Jobs\ServerChecks\SSLCertificateValid::class,
        'description' => 'Checks whether the SSL certificate is currently valid.',
    ],
    'SSL_expiration' => [
        'job' => App\Jobs\ServerChecks\SSLExpiration::class,
        'description' => 'Warns when the certificate is close to expiring.',
    ],
    'ContentRegex' => [
        'job' => App\Jobs\ServerChecks\ContentRegex::class,
        'description' => 'Matches the response body against a regex pattern.',
    ],
    'Latency' => [
        'job' => App\Jobs\ServerChecks\Latency::class,
        'description' => 'Measures request latency and flags thresholds.',
    ],
    'Headers' => [
        'job' => App\Jobs\ServerChecks\Headers::class,
        'description' => 'Validates required response headers.',
    ],
];
