<?php

namespace App\Jobs\ServerChecks;

class Headers extends BaseServerCheckJob
{
    protected function checkName(): string
    {
        return 'Headers';
    }

    public static function defaults(): array
    {
        return [
            'enabled' => false,
            'input' => ['required' => []],
        ];
    }

    protected function perform(array $payload, array $settings): void
    {
        $required = $settings['input']['required'] ?? [];
        $headers = $payload['curl']['headers'] ?? [];

        if (empty($required) || ! is_array($required)) {
            $this->record($this->checkName(), 'success', 'No headers were required.', $settings);

            return;
        }

        $failures = [];
        foreach ($required as $name => $expectation) {
            $key = strtolower($name);
            if (! array_key_exists($key, $headers)) {
                $failures[] = "{$name} is missing";

                continue;
            }

            $value = (string) $headers[$key];
            if ($expectation === null || $expectation === '') {
                continue;
            }

            if ($this->isRegex($expectation)) {
                if (@preg_match($expectation, $value) !== 1) {
                    $failures[] = "{$name} value '{$value}' does not match {$expectation}";
                }
            } elseif ($value !== $expectation) {
                $failures[] = "{$name} value '{$value}' does not match expected '{$expectation}'";
            }
        }

        if (! empty($failures)) {
            $this->record($this->checkName(), 'fail', implode('; ', $failures), $settings);

            return;
        }

        $this->record($this->checkName(), 'success', 'All required headers are present.', $settings);
    }

    protected function isRegex(string $pattern): bool
    {
        return str_starts_with($pattern, '/') && str_ends_with($pattern, '/');
    }
}
