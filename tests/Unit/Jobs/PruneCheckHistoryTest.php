<?php

use App\Jobs\Maintenance\PruneCheckHistory;
use App\Models\CheckHistory;
use App\Models\Server;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('prunes check history records older than retention window', function () {
    $server = Server::factory()->create();

    $recent = CheckHistory::create([
        'server_id' => $server->id,
        'name' => 'Latency',
        'status' => 'success',
        'message' => 'ok',
        'run_curl_batch_id' => (string) Str::uuid(),
        'server_checks_batch_id' => (string) Str::uuid(),
        'check_settings' => json_encode([]),
    ]);
    $recent->forceFill([
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDays(2),
    ])->save();

    $stale = CheckHistory::create([
        'server_id' => $server->id,
        'name' => 'Latency',
        'status' => 'fail',
        'message' => 'slow',
        'run_curl_batch_id' => (string) Str::uuid(),
        'server_checks_batch_id' => (string) Str::uuid(),
        'check_settings' => json_encode([]),
    ]);
    $stale->forceFill([
        'created_at' => now()->subDays(10),
        'updated_at' => now()->subDays(10),
    ])->save();

    $settings = GeneralSettings::fake([
        'check_history_retention_days' => 5,
    ]);

    $job = new PruneCheckHistory;
    $job->handle($settings);

    expect(CheckHistory::find($recent->id))->not->toBeNull();
    expect(CheckHistory::find($stale->id))->toBeNull();
});
