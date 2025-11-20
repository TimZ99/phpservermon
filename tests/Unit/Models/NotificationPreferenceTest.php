<?php

use App\Models\NotificationPreference;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('casts boolean and datetime fields and exposes relations', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create();

    $preference = NotificationPreference::create([
        'user_id' => $user->id,
        'server_id' => $server->id,
        'check_name' => '__OVERALLSTATUS__',
        'channel' => 'mail',
        'enabled' => false,
        'muted_until' => Carbon::now()->addMinutes(5),
    ]);

    expect($preference->enabled)->toBeFalse()
        ->and($preference->muted_until)->toBeInstanceOf(Carbon::class)
        ->and($preference->user->is($user))->toBeTrue()
        ->and($preference->server->is($server))->toBeTrue();
});
