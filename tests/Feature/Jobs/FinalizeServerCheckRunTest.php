<?php

use App\Jobs\FinalizeServerCheckRun;
use App\Models\CheckHistory;
use App\Models\Server;
use App\Models\User;
use App\Notifications\ServerStatusChanged;
use App\Services\Notifications\NotificationPreferenceService;
use App\Services\ServerChecks\ServerCheckRunStore;
use App\Settings\NotificationSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    NotificationSettings::fake([
        'email_global_enabled' => true,
        'telegram_global_enabled' => false,
    ]);

    Notification::fake();
});

function seedCheckHistory(Server $server, string $runId, array $entries): void
{
    foreach ($entries as $entry) {
        CheckHistory::create([
            'server_id' => $server->id,
            'name' => $entry['name'],
            'status' => $entry['status'],
            'message' => $entry['message'],
            'run_curl_batch_id' => $runId,
            'server_checks_batch_id' => $runId,
            'check_settings' => json_encode([]),
        ]);
    }
}

it('stores overall status history and notifies assigned users when status changes', function () {
    $server = Server::factory()->create(['overall_status' => 'success']);
    $user = User::factory()->create(['email' => 'ops@example.com']);
    $server->users()->attach($user);

    $runId = (string) Str::uuid();
    seedCheckHistory($server, $runId, [
        ['name' => 'Latency', 'status' => 'fail', 'message' => 'Very slow'],
        ['name' => 'StatusCode', 'status' => 'success', 'message' => 'OK'],
    ]);

    $store = app(ServerCheckRunStore::class);
    $store->put($runId, ['checks' => ['Latency', 'StatusCode']]);

    $job = new FinalizeServerCheckRun($server->id, $runId, ['Latency', 'StatusCode']);
    $job->handle($store, app(NotificationPreferenceService::class));

    expect(CheckHistory::where('name', FinalizeServerCheckRun::OVERALL_STATUS)->count())->toBe(1);

    $overallHistory = CheckHistory::where('name', FinalizeServerCheckRun::OVERALL_STATUS)->first();
    expect($overallHistory->status)->toBe('fail')
        ->and($overallHistory->message)->toBe('Overall status FAIL after 2 checks (1 fail, 0 warning).');

    expect($server->fresh()->overall_status)->toBe('fail');

    Notification::assertSentTo(
        $user,
        ServerStatusChanged::class,
        fn ($notification) => $notification->status === 'fail'
    );
});

it('does not notify users when overall status stays the same', function () {
    $server = Server::factory()->create(['overall_status' => 'success']);
    $user = User::factory()->create(['email' => 'ops@example.com']);
    $server->users()->attach($user);

    $runId = (string) Str::uuid();
    seedCheckHistory($server, $runId, [
        ['name' => 'Latency', 'status' => 'success', 'message' => 'Fast'],
    ]);

    $store = app(ServerCheckRunStore::class);
    $store->put($runId, ['checks' => ['Latency']]);

    $job = new FinalizeServerCheckRun($server->id, $runId, ['Latency']);
    $job->handle($store, app(NotificationPreferenceService::class));

    expect($server->fresh()->overall_status)->toBe('success');

    Notification::assertNothingSent();
});

it('falls back to success when there are no check results', function () {
    $server = Server::factory()->create(['overall_status' => 'warning']);

    $runId = (string) Str::uuid();
    $store = app(ServerCheckRunStore::class);
    $store->put($runId, ['checks' => []]);

    $job = new FinalizeServerCheckRun($server->id, $runId);
    $job->handle($store, app(NotificationPreferenceService::class));

    $overallHistory = CheckHistory::where('name', FinalizeServerCheckRun::OVERALL_STATUS)->first();
    expect($overallHistory->status)->toBe('success')
        ->and($overallHistory->message)->toBe('Overall status SUCCESS with no checks executed.');
});

it('reports warning overall when only warning statuses are present', function () {
    $server = Server::factory()->create(['overall_status' => 'success']);
    $user = User::factory()->create(['email' => 'ops@example.com']);
    $server->users()->attach($user);

    $runId = (string) Str::uuid();
    seedCheckHistory($server, $runId, [
        ['name' => 'ContentRegex', 'status' => 'warning', 'message' => 'Missing pattern'],
        ['name' => 'Latency', 'status' => 'success', 'message' => 'OK'],
    ]);

    $store = app(ServerCheckRunStore::class);
    $store->put($runId, ['checks' => ['ContentRegex', 'Latency']]);

    $job = new FinalizeServerCheckRun($server->id, $runId, ['ContentRegex', 'Latency']);
    $job->handle($store, app(NotificationPreferenceService::class));

    $overallHistory = CheckHistory::where('name', FinalizeServerCheckRun::OVERALL_STATUS)->first();
    expect($overallHistory->status)->toBe('warning');
    expect($server->fresh()->overall_status)->toBe('warning');
});

it('skips server updates when the server record is missing', function () {
    $server = Server::factory()->create(['overall_status' => 'success']);

    $runId = (string) Str::uuid();
    seedCheckHistory($server, $runId, [
        ['name' => 'Latency', 'status' => 'success', 'message' => 'OK'],
    ]);

    $store = app(ServerCheckRunStore::class);
    $store->put($runId, ['checks' => ['Latency']]);

    $serverId = $server->id;
    $server->delete();

    $job = new FinalizeServerCheckRun($serverId, $runId, ['Latency']);
    $job->handle($store, app(NotificationPreferenceService::class));

    expect(CheckHistory::where('name', FinalizeServerCheckRun::OVERALL_STATUS)->exists())->toBeTrue();
    Notification::assertNothingSent();
});
