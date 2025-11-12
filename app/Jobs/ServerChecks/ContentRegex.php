<?php

namespace App\Jobs\ServerChecks;

class ContentRegex extends BaseServerCheckJob
{
    protected function checkName(): string
    {
        return 'ContentRegex';
    }

    protected function perform(array $payload, array $settings): void
    {
        $pattern = $settings['input']['pattern'] ?? null;
        $body = $payload['curl']['body'] ?? '';

        if (! is_string($pattern) || $pattern === '') {
            $this->record($this->checkName(), 'warning', 'No regex pattern configured.', $settings);

            return;
        }

        $matched = @preg_match($pattern, (string) $body) === 1;
        $status = $matched ? 'success' : 'fail';
        $message = $matched ? 'Content matched the expected pattern.' : 'Pattern '.$pattern.' was not found in the response.';

        $this->record($this->checkName(), $status, $message, $settings);
    }
}
