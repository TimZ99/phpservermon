<?php

namespace App\Services\ServerChecks;

use App\Models\Server;

class CheckSettingsResolver
{
    /**
     * @param  array<string>  $limitTo
     * @return array<string>
     */
    public function enabledChecks(Server $server, array $limitTo = []): array
    {
        $settings = $server->check_settings ?? [];
        $checks = [];

        foreach ($settings as $name => $config) {
            if (! empty($limitTo) && ! in_array($name, $limitTo, true)) {
                continue;
            }

            if (! is_array($config)) {
                continue;
            }

            if (($config['enabled'] ?? false) !== true) {
                continue;
            }

            $checks[] = $name;
        }

        return array_values(array_unique($checks));
    }

    public function configFor(Server $server, string $checkName): array
    {
        $settings = $server->check_settings ?? [];

        return $settings[$checkName] ?? [];
    }
}
