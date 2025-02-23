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

        $json = json_encode([
            'SSL' => [
                'enabled' => true,
                'nested' => true,
                'SSL_expiration' => [
                    'enabled' => true,
                    'type' => 'warning',
                    'input' => ['days' => 5],
                ],
                'SSL_certificate_valid' => [
                    'enabled' => true,
                    'type' => 'error',
                    'input' => [],
                ],
            ],
            'StatusCode' => [
                'enabled' => true,
                'type' => 'error',
                'input' => [],
            ],
        ]);

        return [
            'name' => $status.' '.fake()->word(),
            'ip' => 'https://httpstat.us/'.$status,
            'check_settings' => $json,
        ];
    }
}
