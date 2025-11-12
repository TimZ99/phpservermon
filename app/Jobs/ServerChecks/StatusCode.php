<?php

namespace App\Jobs\ServerChecks;

class StatusCode extends BaseServerCheckJob
{
    protected function checkName(): string
    {
        return 'StatusCode';
    }

    public static function defaults(): array
    {
        return ['enabled' => true];
    }

    protected function perform(array $payload, array $settings): void
    {
        $info = $payload['curl']['info'] ?? [];
        $code = (int) ($info['http_code'] ?? 0);
        $message = 'Unhandled status code '.$code;
        $status = 'fail';

        if ($code === 0) {
            $message = 'No response from server';
        } elseif ($code >= 200 && $code <= 399) {
            $status = 'success';
            $message = 'Status code OK ('.$code.')';
        } elseif ($code >= 400 && $code <= 499) {
            $message = 'Client error ('.$code.')';
        } elseif ($code >= 500 && $code <= 599) {
            $message = 'Server error ('.$code.')';
        }

        $this->record($this->checkName(), $status, $message, $settings);
    }
}
