<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use App\Models\Server;

it('allows admin to pass admin-only gate', function () {
    $admin = User::factory()->create(['admin' => true]);
    $this->actingAs($admin);
    $response = Gate::inspect('admin-only');
    expect($response->allowed())->toBeTrue();
    expect($response->message())->toBeNull();
});

it('denies user to pass admin-only gate', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = Gate::inspect('admin-only');
    expect($response->denied())->toBeTrue();
    expect($response->message())->toBeString();
});

it('allows non-suspended admin to pass not-suspended gate', function () {
    $admin = User::factory()->create(['admin' => true, 'suspended' => false]);
    $this->actingAs($admin);
    $response = Gate::inspect('not-suspended');
    expect($response->allowed())->toBeTrue();
    expect($response->message())->toBeNull();
});

it('won\'t allow suspended admin to pass not-suspended gate', function () {
    $admin = User::factory()->create(['admin' => true, 'suspended' => true]);
    $this->actingAs($admin);
    $response = Gate::inspect('not-suspended');
    expect($response->denied())->toBeTrue();
    expect($response->message())->toBeString();
});

it('allows non-suspended user to pass not-suspended gate', function () {
    $user = User::factory()->create(['suspended' => false]);
    $this->actingAs($user);
    $response = Gate::inspect('not-suspended');
    expect($response->allowed())->toBeTrue();
    expect($response->message())->toBeNull();
});

it('won\'t allow suspended user to pass not-suspended gate', function () {
    $user = User::factory()->create(['suspended' => true]);
    $this->actingAs($user);
    $response = Gate::inspect('not-suspended');
    expect($response->denied())->toBeTrue();
    expect($response->message())->toBeString();
});

it('allows admin access to servers they have a relation with', function () {
    $admin = User::factory()->has(Server::factory())->create(['admin' => true]);
    $server = $admin->servers->first();
    $this->actingAs($admin);
    $response = Gate::inspect('user-connected-to-server', $server);
    expect($response->allowed())->toBeTrue();
    expect($response->message())->toBeNull();
});

it('restricts admin to servers they have a relation with', function () {
    $admin = User::factory()->has(Server::factory())->create(['admin' => true]);
    $server = Server::factory()->create();
    $this->actingAs($admin);
    $response = Gate::inspect('user-connected-to-server', $server);
    expect($response->denied())->toBeTrue();
    expect($response->message())->toBeString();
});

it('allows user access to servers they have a relation with', function () {
    $user = User::factory()->has(Server::factory())->create();
    $server = $user->servers->first();
    $this->actingAs($user);
    $response = Gate::inspect('user-connected-to-server', $server);
    expect($response->allowed())->toBeTrue();
    expect($response->message())->toBeNull();
});

it('restricts user to servers they have a relation with', function () {
    $user = User::factory()->has(Server::factory())->create();
    $server = Server::factory()->create();
    $this->actingAs($user);
    $response = Gate::inspect('user-connected-to-server', $server);
    expect($response->denied())->toBeTrue();
    expect($response->message())->toBeString();
});
