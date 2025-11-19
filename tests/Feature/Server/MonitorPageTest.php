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
                'nested-secondary' => ['enabled' => true],
            ],
            'Latency' => ['enabled' => true],
            'StatusCode' => ['enabled' => true],
        ],
    ]);
    $server->users()->attach($user);

    foreach ([
        ['name' => 'nested-warning', 'status' => 'warning', 'timestamp' => now()->subMinutes(2)],
        ['name' => 'nested-danger', 'status' => 'danger', 'timestamp' => now()->subMinute()],
        ['name' => 'nested-secondary', 'status' => 'unknown', 'timestamp' => now()],
        ['name' => 'Latency', 'status' => 'unknown', 'timestamp' => now()],
        ['name' => 'StatusCode', 'status' => 'success', 'timestamp' => now()],
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
    expect($status)->toContain(['name' => 'nested-secondary', 'css' => 'secondary', 'color' => '#ddd']);
    expect($status)->toContain(['name' => 'StatusCode', 'css' => 'success', 'color' => '#28a745']);
    expect($status)->toContain(['name' => 'Latency', 'css' => 'danger', 'color' => '#dc3545']);
});

it('skips disabled checks when building monitor page status list', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create([
        'check_settings' => [
            'Latency' => ['enabled' => true],
            'DisabledCheck' => ['enabled' => false],
        ],
    ]);
    $server->users()->attach($user);

    CheckHistory::create([
        'server_id' => $server->id,
        'run_curl_batch_id' => (string) Str::uuid(),
        'server_checks_batch_id' => (string) Str::uuid(),
        'name' => 'Latency',
        'status' => 'success',
        'message' => 'ok',
        'check_settings' => json_encode([]),
    ]);

    CheckHistory::create([
        'server_id' => $server->id,
        'run_curl_batch_id' => (string) Str::uuid(),
        'server_checks_batch_id' => (string) Str::uuid(),
        'name' => 'DisabledCheck',
        'status' => 'success',
        'message' => 'should skip',
        'check_settings' => json_encode([]),
    ]);

    $response = actingAs($user)->get(route('server.monitor'))->assertOk();
    $serverData = $response->viewData('servers')->firstWhere('id', $server->id);

    expect(collect($serverData->show_status)->pluck('name'))->toContain('Latency')
        ->and(collect($serverData->show_status)->pluck('name'))->not()->toContain('DisabledCheck');
});

it('applies css mapping for nested success/warning/danger statuses', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create([
        'check_settings' => [
            'Http' => [
                'enabled' => true,
                'nested' => true,
                'nested-success' => ['enabled' => true],
                'nested-warning' => ['enabled' => true],
                'nested-danger' => ['enabled' => true],
            ],
        ],
    ]);
    $server->users()->attach($user);

    foreach ([
        ['name' => 'nested-success', 'status' => 'success'],
        ['name' => 'nested-warning', 'status' => 'warning'],
        ['name' => 'nested-danger', 'status' => 'danger'],
    ] as $payload) {
        CheckHistory::create([
            'server_id' => $server->id,
            'run_curl_batch_id' => (string) Str::uuid(),
            'server_checks_batch_id' => (string) Str::uuid(),
            'name' => $payload['name'],
            'status' => $payload['status'],
            'message' => 'status',
            'check_settings' => json_encode([]),
        ]);
    }

    $response = actingAs($user)->get(route('server.monitor'))->assertOk();
    $status = collect($response->viewData('servers')->firstWhere('id', $server->id)->show_status);

    expect($status)->toContain(['name' => 'nested-success', 'css' => 'success', 'color' => '#28a745']);
    expect($status)->toContain(['name' => 'nested-warning', 'css' => 'warning', 'color' => '#ffc107']);
    expect($status)->toContain(['name' => 'nested-danger', 'css' => 'danger', 'color' => '#dc3545']);
});

it('uses top-level status css mapping for success and warning', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create([
        'check_settings' => [
            'StatusCode' => ['enabled' => true],
            'Latency' => ['enabled' => true],
        ],
    ]);
    $server->users()->attach($user);

    foreach ([
        ['name' => 'StatusCode', 'status' => 'success'],
        ['name' => 'Latency', 'status' => 'warning'],
    ] as $payload) {
        CheckHistory::create([
            'server_id' => $server->id,
            'run_curl_batch_id' => (string) Str::uuid(),
            'server_checks_batch_id' => (string) Str::uuid(),
            'name' => $payload['name'],
            'status' => $payload['status'],
            'message' => 'status',
            'check_settings' => json_encode([]),
        ]);
    }

    $response = actingAs($user)->get(route('server.monitor'))->assertOk();
    $status = collect($response->viewData('servers')->firstWhere('id', $server->id)->show_status);

    expect($status)->toContain(['name' => 'StatusCode', 'css' => 'success', 'color' => '#28a745']);
    expect($status)->toContain(['name' => 'Latency', 'css' => 'warning', 'color' => '#ffc107']);
});
