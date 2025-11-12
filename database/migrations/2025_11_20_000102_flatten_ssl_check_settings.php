<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('servers')->select('id', 'check_settings')->orderBy('id')->chunk(100, function ($servers): void {
            foreach ($servers as $server) {
                $settings = json_decode($server->check_settings ?? '{}', true);
                if (! is_array($settings) || ! array_key_exists('SSL', $settings)) {
                    continue;
                }

                $ssl = $settings['SSL'];
                if (! isset($settings['SSL_active'])) {
                    $settings['SSL_active'] = ['enabled' => (bool) ($ssl['enabled'] ?? false)];
                }

                if (isset($ssl['SSL_certificate_valid'])) {
                    $settings['SSL_certificate_valid'] = $ssl['SSL_certificate_valid'];
                }

                if (isset($ssl['SSL_expiration'])) {
                    $settings['SSL_expiration'] = $ssl['SSL_expiration'];
                }

                unset($settings['SSL']);
                DB::table('servers')
                    ->where('id', $server->id)
                    ->update(['check_settings' => json_encode($settings)]);
            }
        });
    }

    public function down(): void
    {
        // No-op; flattening is irreversible without losing data.
    }
};
