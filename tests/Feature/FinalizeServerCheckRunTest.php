<?php

use App\Jobs\FinalizeServerCheckRun;
use App\Models\CheckHistory;
use App\Models\NotificationPreference;
use App\Models\Server;
use App\Models\User;
use App\Notifications\ServerStatusChanged;
use App\Services\Notifications\NotificationPreferenceService;
use App\Services\ServerChecks\ServerCheckRunStore;
use App\Settings\NotificationSettings;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

function fakeNotificationSettings(): void
{
    $settings = new NotificationSettings;
    $settings->email_global_enabled = true;
    $settings->telegram_global_enabled = false;
    app()->instance(NotificationSettings::class, $settings);
}

it('records overall status and notifies linked users', function () {
    Notification::fake();
    fakeNotificationSettings();

    $user = User::factory()->create(['email' => 'status@example.com']);
    $server = Server::factory()->create(['overall_status' => 'success']);
    $server->users()->attach($user);

    $runId = (string) Str::uuid();
    CheckHistory::create([
        'server_id' => $server->id,
        'run_curl_batch_id' => $runId,
        'server_checks_batch_id' => $runId,
        'name' => 'StatusCode',
        'status' => 'fail',
        'message' => 'Failed response',
    ]);

    $job = new FinalizeServerCheckRun($server->id, $runId, ['StatusCode']);
    $job->handle(app(ServerCheckRunStore::class), app(NotificationPreferenceService::class));

    Notification::assertSentTo($user, ServerStatusChanged::class, function ($notification) use ($server) {
        return $notification->server->is($server) && $notification->status === 'fail';
    });

    $server->refresh();
    expect($server->overall_status)->toBe('fail');

    $history = CheckHistory::where('run_curl_batch_id', $runId)
        ->where('name', FinalizeServerCheckRun::OVERALL_STATUS)
        ->first();
    expect($history)->not->toBeNull()->and($history->status)->toBe('fail');
});

it('respects per-server mute preferences for future runs', function () {
    Notification::fake();
    fakeNotificationSettings();

    $user = User::factory()->create(['email' => 'mute@example.com']);
    $server = Server::factory()->create(['overall_status' => 'fail']);
    $server->users()->attach($user);

    $runIdFail = (string) Str::uuid();
    CheckHistory::create([
        'server_id' => $server->id,
        'run_curl_batch_id' => $runIdFail,
        'server_checks_batch_id' => $runIdFail,
        'name' => 'StatusCode',
        'status' => 'fail',
        'message' => 'Still failing',
    ]);

    $jobFail = new FinalizeServerCheckRun($server->id, $runIdFail, ['StatusCode']);
    $jobFail->handle(app(ServerCheckRunStore::class), app(NotificationPreferenceService::class));

    Notification::fake();

    NotificationPreference::create([
        'user_id' => $user->id,
        'server_id' => $server->id,
        'check_name' => FinalizeServerCheckRun::OVERALL_STATUS,
        'channel' => 'mail',
        'enabled' => false,
    ]);

    $runIdSuccess = (string) Str::uuid();
    CheckHistory::create([
        'server_id' => $server->id,
        'run_curl_batch_id' => $runIdSuccess,
        'server_checks_batch_id' => $runIdSuccess,
        'name' => 'StatusCode',
        'status' => 'success',
        'message' => 'Recovered',
    ]);

    $jobSuccess = new FinalizeServerCheckRun($server->id, $runIdSuccess, ['StatusCode']);
    $jobSuccess->handle(app(ServerCheckRunStore::class), app(NotificationPreferenceService::class));

    Notification::assertNothingSent();
    $server->refresh();
    expect($server->overall_status)->toBe('success');
});
