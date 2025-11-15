<?php

namespace App\Jobs\ServerChecks;

class SSLActive extends BaseServerCheckJob
{
    protected function checkName(): string
    {
        return 'SSL_active';
    }

    public static function defaults(): array
    {
        return ['enabled' => true];
    }

    protected function perform(array $payload, array $settings): void
    {
        $url = $payload['curl']['info']['url'] ?? $payload['server']['ip'] ?? '';
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $isHttps = $scheme === 'https';

        $status = $isHttps ? 'success' : 'fail';
        $message = $isHttps ? 'HTTPS is active for this endpoint.' : 'HTTPS is not active.';

        $this->record($this->checkName(), $status, $message, $settings);
    }
}
