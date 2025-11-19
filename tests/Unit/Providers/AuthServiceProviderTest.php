<?php

use App\Models\Server;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    AuthServiceProvider::boot();
});

it('throws when calling an undefined policy method', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create();

    expect(fn () => Gate::forUser($user)->authorize('nonexistentAbility', $server))
        ->toThrow(AuthorizationException::class);
});

it('throws when calling an undefined gate', function () {
    $user = User::factory()->create();

    expect(fn () => Gate::forUser($user)->authorize('undefined-gate'))
        ->toThrow(AuthorizationException::class);
});

it('allows config manage gate when scope present', function () {
    $user = User::factory()->create();
    $user->setScope(['config:manage']);
    $user->save();

    $response = Gate::forUser($user)->inspect('config:manage');
    expect($response->allowed())->toBeTrue();

    $other = User::factory()->create();
    $deny = Gate::forUser($other)->inspect('config:manage');
    expect($deny->denied())->toBeTrue();
});

it('denies suspended users via not-suspended gate', function () {
    $active = User::factory()->create(['suspended' => false]);
    $response = Gate::forUser($active)->inspect('not-suspended');
    expect($response->allowed())->toBeTrue();

    $suspended = User::factory()->create(['suspended' => true]);
    $denied = Gate::forUser($suspended)->inspect('not-suspended');
    expect($denied->denied())->toBeTrue();
});
