<?php

namespace App\Jobs\ServerChecks;

use App\Jobs\ServerChecks\Concerns\InteractsWithCertificate;

class SSLCertificateValid extends BaseServerCheckJob
{
    use InteractsWithCertificate;

    protected function checkName(): string
    {
        return 'SSL_certificate_valid';
    }

    public static function defaults(): array
    {
        return ['enabled' => true];
    }

    protected function perform(array $payload, array $settings): void
    {
        $certificate = $this->extractCertificate($payload);

        if (! $certificate) {
            $this->record($this->checkName(), 'fail', 'No SSL certificate presented.', $settings);

            return;
        }

        $validUntil = $certificate['validTo_time_t'] ?? null;
        $isValid = is_int($validUntil) && $validUntil > time();
        $status = $isValid ? 'success' : 'fail';
        $message = $isValid
            ? 'SSL certificate is currently valid.'
            : 'SSL certificate has expired.';

        $this->record($this->checkName(), $status, $message, $settings);
    }
}
