<?php

namespace App\Jobs\ServerChecks\Concerns;

trait InteractsWithCertificate
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    protected function extractCertificate(array $payload): ?array
    {
        $certInfo = $payload['curl']['info']['certinfo'] ?? [];
        $cert = $certInfo[0]['Cert'] ?? null;

        if (! is_string($cert)) {
            return null;
        }

        $parsed = openssl_x509_parse($cert);

        if ($parsed === false) {
            return null;
        }

        return $parsed;
    }
}
