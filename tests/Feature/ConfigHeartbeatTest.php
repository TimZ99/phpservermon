<?php

use App\Enums\QueueName;
use App\Models\User;
use App\Services\Queue\QueueHeartbeatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Mockery as M;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

function configManager(): User
{
    $user = User::factory()->create();
    $user->scopes = ['config:manage'];
    $user->save();

    return $user;
}

afterEach(function () {
    M::close();
});

it('returns heartbeat status when queue driver is database', function () {
    Config::set('queue.default', 'database');

    $service = M::mock(QueueHeartbeatService::class);
    $service->shouldReceive('isAlive')->once()->with(QueueName::CURL)->andReturnTrue();
    $service->shouldReceive('lastBeat')->once()->with(QueueName::CURL)->andReturn(Carbon::now());
    app()->instance(QueueHeartbeatService::class, $service);

    actingAs(configManager());

    getJson(route('config.heartbeat'))
        ->assertOk()
        ->assertJson(['alive' => true]);
});

it('returns heartbeat missing status when service reports stale', function () {
    Config::set('queue.default', 'database');

    $service = M::mock(QueueHeartbeatService::class);
    $service->shouldReceive('isAlive')->once()->with(QueueName::CURL)->andReturnFalse();
    $service->shouldReceive('lastBeat')->once()->with(QueueName::CURL)->andReturn(null);
    app()->instance(QueueHeartbeatService::class, $service);

    actingAs(configManager());

    getJson(route('config.heartbeat'))
        ->assertOk()
        ->assertJson(['alive' => false]);
});

it('shows queue driver badge when not using database connection', function () {
    Config::set('queue.default', 'redis');

    actingAs(configManager());

    get(route('config.edit'))
        ->assertOk()
        ->assertSee('Queue driver: redis');
});
