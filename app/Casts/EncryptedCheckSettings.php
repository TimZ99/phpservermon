<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class EncryptedCheckSettings implements CastsAttributes
{
    private const PREFIX = 'ENC:';

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return [];
        }

        $decoded = json_decode((string) $value, true);
        if (! is_array($decoded)) {
            return [];
        }

        return $this->traverse($decoded, decrypt: true);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if (is_string($value)) {
            $value = json_decode($value, true) ?? [];
        }

        $encoded = $this->traverse((array) $value, decrypt: false);

        return json_encode($encoded);
    }

    /**
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    protected function traverse(array $data, bool $decrypt): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->traverse($value, $decrypt);

                continue;
            }

            if (! is_string($value)) {
                $data[$key] = $value;

                continue;
            }

            $data[$key] = $decrypt
                ? $this->maybeDecrypt($value)
                : $this->maybeEncrypt($value, (string) $key);
        }

        return $data;
    }

    protected function maybeEncrypt(string $value, string $key): string
    {
        if (! $this->shouldEncrypt($key) || str_starts_with($value, self::PREFIX)) {
            return $value;
        }

        return self::PREFIX.base64_encode(Crypt::encryptString($value));
    }

    protected function maybeDecrypt(string $value): string
    {
        if (! str_starts_with($value, self::PREFIX)) {
            return $value;
        }

        $payload = substr($value, strlen(self::PREFIX));

        try {
            $decoded = base64_decode($payload, true);
            if ($decoded === false) {
                return $value;
            }

            return Crypt::decryptString($decoded);
        } catch (\Throwable) {
            return $value;
        }
    }

    protected function shouldEncrypt(string $key): bool
    {
        $lower = Str::lower($key);

        $needles = [
            'token',
            'secret',
            'authorization',
            'auth_header',
            'auth',
            'password',
            'api_key',
            'x-api-key',
        ];

        foreach ($needles as $needle) {
            if (str_contains($lower, $needle)) {
                return true;
            }
        }

        return false;
    }
}
