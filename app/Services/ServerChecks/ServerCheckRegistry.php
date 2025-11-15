<?php

namespace App\Services\ServerChecks;

class ServerCheckRegistry
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return config('server-checks', []);
    }

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return collect($this->all())
            ->map(function (array $definition) {
                $jobClass = $definition['job'] ?? null;
                if (is_string($jobClass) && method_exists($jobClass, 'defaults')) {
                    return $jobClass::defaults();
                }

                return ['enabled' => false];
            })
            ->toArray();
    }
}
