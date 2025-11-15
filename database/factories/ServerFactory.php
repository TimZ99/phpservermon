<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Server>
 */
class ServerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = [200, 401, 403, 404];
        $status = $statuses[array_rand($statuses)];

        $json = [
            'StatusCode' => [
                'enabled' => true,
            ],
            'SSL_active' => [
                'enabled' => true,
            ],
            'SSL_certificate_valid' => [
                'enabled' => true,
            ],
            'SSL_expiration' => [
                'enabled' => true,
                'input' => ['days' => 5],
            ],
            'ContentRegex' => [
                'enabled' => false,
                'input' => ['pattern' => '/.+/'],
            ],
            'Latency' => [
                'enabled' => true,
                'input' => [
                    'warning_ms' => 600,
                    'fail_ms' => 1500,
                ],
            ],
            'Headers' => [
                'enabled' => false,
                'input' => [
                    'required' => [],
                ],
            ],
        ];

        return [
            'name' => $status.' '.fake()->word(),
            'ip' => 'https://httpstat.us/'.$status,
            'check_settings' => $json,
        ];
    }
}
