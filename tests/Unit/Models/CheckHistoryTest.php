<?php

use App\Models\CheckHistory;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates check histories with uuid keys and server relation', function () {
    $server = Server::factory()->create();

    $history = CheckHistory::create([
        'server_id' => $server->id,
        'name' => 'Latency',
        'status' => 'success',
        'message' => 'All good',
        'run_curl_batch_id' => (string) Illuminate\Support\Str::uuid(),
        'server_checks_batch_id' => (string) Illuminate\Support\Str::uuid(),
        'check_settings' => json_encode(['Latency' => ['enabled' => true]]),
    ]);

    expect($history->getKey())->toBeString()
        ->and($history->exists)->toBeTrue()
        ->and($history->server)->not->toBeNull()
        ->and($history->server->is($server))->toBeTrue();

    expect($history->incrementing)->toBeFalse();
    expect($history->getKeyType())->toBe('string');
});
