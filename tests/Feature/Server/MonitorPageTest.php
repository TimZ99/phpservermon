<?php

use App\Models\CheckHistory;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('renders monitor page with computed check statuses', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create([
        'check_settings' => [
            'Http' => [
                'enabled' => true,
                'nested' => true,
                'nested-warning' => ['enabled' => true],
                'nested-danger' => ['enabled' => true],
            ],
            'Latency' => ['enabled' => true],
        ],
    ]);
    $server->users()->attach($user);

    foreach ([
        ['name' => 'nested-warning', 'status' => 'warning', 'timestamp' => now()->subMinutes(2)],
        ['name' => 'nested-danger', 'status' => 'danger', 'timestamp' => now()->subMinute()],
        ['name' => 'Latency', 'status' => 'unknown', 'timestamp' => now()],
    ] as $payload) {
        $history = CheckHistory::create([
            'server_id' => $server->id,
            'run_curl_batch_id' => (string) Str::uuid(),
            'server_checks_batch_id' => (string) Str::uuid(),
            'name' => $payload['name'],
            'status' => $payload['status'],
            'message' => 'status',
            'check_settings' => json_encode([]),
        ]);
        $history->forceFill([
            'created_at' => $payload['timestamp'],
            'updated_at' => $payload['timestamp'],
        ])->save();
    }

    $response = actingAs($user)->get(route('server.monitor'))->assertOk();

    $servers = $response->viewData('servers');
    $status = collect($servers->firstWhere('id', $server->id)->show_status);

    expect($status)->toContain(['name' => 'nested-warning', 'css' => 'warning', 'color' => '#ffc107']);
    expect($status)->toContain(['name' => 'nested-danger', 'css' => 'danger', 'color' => '#dc3545']);
    expect($status)->toContain(['name' => 'Latency', 'css' => 'danger', 'color' => '#dc3545']);
});
