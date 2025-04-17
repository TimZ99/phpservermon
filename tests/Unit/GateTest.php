<?php

use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

// Admin-only gate
it('allows only admins to pass admin-only gate', function () {
    $admin = User::factory()->create(['admin' => true]);
    $this->actingAs($admin);
    $response = Gate::inspect('admin-only');
    expect($response->allowed())->toBeTrue();
    expect($response->message())->toBeNull();

    $user = User::factory()->create();
    $this->actingAs($user);
    $response = Gate::inspect('admin-only');
    expect($response->denied())->toBeTrue();
    expect($response->message())->toBeString();
});

// Not-suspended gate
it('allows non-suspended (admin) to pass not-suspended gate', function () {
    // Y admin
    // N suspended
    // expects to allow
    $admin = User::factory()->create(['admin' => true, 'suspended' => false]);
    $this->actingAs($admin);
    $response = Gate::inspect('not-suspended');
    expect($response->allowed())->toBeTrue();
    expect($response->message())->toBeNull();

    // Y admin
    // Y suspended
    // expects to denie
    $admin = User::factory()->create(['admin' => true, 'suspended' => true]);
    $this->actingAs($admin);
    $response = Gate::inspect('not-suspended');
    expect($response->denied())->toBeTrue();
    expect($response->message())->toBeString();

    // N admin
    // N suspended
    // expects to allow
    $user = User::factory()->create(['suspended' => false]);
    $this->actingAs($user);
    $response = Gate::inspect('not-suspended');
    expect($response->allowed())->toBeTrue();
    expect($response->message())->toBeNull();

    // N admin
    // Y suspended
    // expects to fail
    $user = User::factory()->create(['suspended' => true]);
    $this->actingAs($user);
    $response = Gate::inspect('not-suspended');
    expect($response->denied())->toBeTrue();
    expect($response->message())->toBeString();
});

// User-connected-to-server gate
it('only allows user access to servers they have a relation with, regardless of there admin status', function () {
    $user = User::factory()->has(Server::factory())->create();
    $admin = User::factory()->has(Server::factory())->create(['admin' => true]);
    $serverWithoutRelationship = Server::factory()->create();
    $serverWithRelationToUser = $user->servers->first();
    $serverWithRelationToAdmin = $admin->servers->first();

    // N admin
    // Y relation
    $this->actingAs($user);
    $response = Gate::inspect('user-connected-to-server', $serverWithRelationToUser);
    expect($response->allowed())->toBeTrue();
    expect($response->message())->toBeNull();

    // N admin
    // N relation
    $this->actingAs($user);
    $response = Gate::inspect('user-connected-to-server', $serverWithoutRelationship);
    expect($response->denied())->toBeTrue();
    expect($response->message())->toBeString();

    // Y admin
    // Y relation
    $this->actingAs($admin);
    $response = Gate::inspect('user-connected-to-server', $serverWithRelationToAdmin);
    expect($response->allowed())->toBeTrue();
    expect($response->message())->toBeNull();

    // Y admin
    // N relation
    $this->actingAs($admin);
    $response = Gate::inspect('user-connected-to-server', $serverWithoutRelationship);
    expect($response->denied())->toBeTrue();
    expect($response->message())->toBeString();
});
