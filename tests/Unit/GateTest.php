<?php

use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

// Not-suspended gate
it('allows non-suspended to pass not-suspended gate', function () {

    $user = User::factory()->create(['suspended' => false]);
    $this->actingAs($user);
    $response = Gate::inspect('not-suspended');
    expect($response->allowed())->toBeTrue();
    expect($response->message())->toBeNull();

    $user = User::factory()->create(['suspended' => true]);
    $this->actingAs($user);
    $response = Gate::inspect('not-suspended');
    expect($response->denied())->toBeTrue();
    expect($response->message())->toBeString();
});

// User-connected-to-server gate
it('only allows user access to servers they have a relation with', function () {
    $user = User::factory()->has(Server::factory())->create();
    $serverWithoutRelationship = Server::factory()->create();
    $serverWithRelationToUser = $user->servers->first();

    $this->actingAs($user);
    $response = Gate::inspect('user-connected-to-server', $serverWithRelationToUser);
    expect($response->allowed())->toBeTrue();
    expect($response->message())->toBeNull();

    $response = Gate::inspect('user-connected-to-server', $serverWithoutRelationship);
    expect($response->denied())->toBeTrue();
    expect($response->message())->toBeString();
});
