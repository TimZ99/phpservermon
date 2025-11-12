<?php

namespace App\Jobs\ServerChecks;

class Latency extends BaseServerCheckJob
{
    protected function checkName(): string
    {
        return 'Latency';
    }

    protected function perform(array $payload, array $settings): void
    {
        $latency = $payload['curl']['latency_ms'] ?? null;
        if ($latency === null) {
            $this->record($this->checkName(), 'warning', 'Latency was not reported by cURL.', $settings);

            return;
        }

        $warning = (int) ($settings['input']['warning_ms'] ?? 500);
        $fail = (int) ($settings['input']['fail_ms'] ?? 1500);

        if ($latency >= $fail) {
            $status = 'fail';
            $message = "Latency {$latency}ms exceeds failure threshold ({$fail}ms).";
        } elseif ($latency >= $warning) {
            $status = 'warning';
            $message = "Latency {$latency}ms exceeds warning threshold ({$warning}ms).";
        } else {
            $status = 'success';
            $message = "Latency {$latency}ms is within acceptable limits.";
        }

        $this->record($this->checkName(), $status, $message, $settings);
    }
}
