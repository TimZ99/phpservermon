<?php

namespace App\Jobs\ServerChecks;

use App\Jobs\ServerChecks\Concerns\InteractsWithCertificate;

class SSLExpiration extends BaseServerCheckJob
{
    use InteractsWithCertificate;

    protected function checkName(): string
    {
        return 'SSL_expiration';
    }

    protected function perform(array $payload, array $settings): void
    {
        $certificate = $this->extractCertificate($payload);

        if (! $certificate) {
            $this->record($this->checkName(), 'fail', 'SSL expiration could not be verified because no certificate was found.', $settings);

            return;
        }

        $threshold = (int) ($settings['input']['days'] ?? 5);
        $validUntil = $certificate['validTo_time_t'] ?? null;

        if (! is_int($validUntil)) {
            $this->record($this->checkName(), 'fail', 'SSL expiration date is missing.', $settings);

            return;
        }

        $daysRemaining = (int) floor(($validUntil - time()) / 86400);

        if ($daysRemaining < 0) {
            $this->record($this->checkName(), 'fail', 'Certificate expired '.abs($daysRemaining).' days ago.', $settings);

            return;
        }

        $status = $daysRemaining <= $threshold ? 'warning' : 'success';
        $message = $status === 'warning'
            ? 'Certificate expires in '.$daysRemaining.' days.'
            : 'Certificate validity is OK ('.$daysRemaining.' days remaining).';

        $this->record($this->checkName(), $status, $message, $settings);
    }
}
