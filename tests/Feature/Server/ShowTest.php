<?php

use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('denies server list access to guests', function () {
    get(route('server.index'))
        ->assertRedirect(route('login'));
});

it('denies server list access to users without server:view:* scope', function () {
    $user = User::factory()->create();

    actingAs($user);

    get(route('server.index'))
        ->assertForbidden();
});

it('allows users with server:view:* to view server list', function () {
    $userWithScope = User::factory()->create(['scopes' => ['server:view:*']]);
    $servers = Server::factory(2)->create();

    actingAs($userWithScope);

    get(route('server.index'))
        ->assertOk()
        ->assertSee($servers->first()->name)
        ->assertSee($servers->last()->name)
        ->assertViewIs('server.index');
});

it('displays server status indicators on list page', function () {
    $userWithScope = User::factory()->create(['scopes' => ['server:view:*']]);
    $server = Server::factory()->create();

    actingAs($userWithScope);

    get(route('server.index'))
        ->assertOk()
        ->assertViewHas('servers', function ($viewServers) use ($server) {
            $viewServer = $viewServers->firstWhere('id', $server->id);

            return isset($viewServer->statusCss) && isset($viewServer->statusCssColor);
        });
});

it('denies individual server access to guests', function () {
    $server = Server::factory()->create();

    get(route('server.show', $server))
        ->assertRedirect(route('login'));
});

it('allows assigned users to view server details', function () {
    $user = User::factory()->has(Server::factory())->create();
    $server = $user->servers()->first();

    actingAs($user);

    get(route('server.show', $server))
        ->assertOk()
        ->assertSee($server->name)
        ->assertSee($server->port)
        ->assertSee((string) $server->ip)
        ->assertViewIs('server.show');
});

it('denies access to servers user is not assigned to', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create();

    actingAs($user);

    get(route('server.show', $server))
        ->assertForbidden();
});

it('allows users with server:view:* to view any server', function () {
    $user = User::factory()->create(['scopes' => ['server:view:*']]);
    $server = Server::factory()->create();

    actingAs($user);

    get(route('server.show', $server))
        ->assertOk()
        ->assertSee($server->name);
});

it('allows users with resource-specific view scope to view that server', function () {
    $server = Server::factory()->create();
    $user = User::factory()->create(['scopes' => ["server:view:{$server->id}"]]);

    actingAs($user);

    get(route('server.show', $server))
        ->assertOk()
        ->assertSee($server->name);
});

it('displays server check history on show page', function () {
    $user = User::factory()->create(['scopes' => ['server:view:*']]);
    $server = Server::factory()->create();

    actingAs($user);

    get(route('server.show', $server))
        ->assertOk()
        ->assertViewHas('server', fn ($viewServer) => $viewServer->id === $server->id)
        ->assertViewHas('checkSettings')
        ->assertViewHas('checkDefinitions');
});

it('displays assigned users on show page', function () {
    $assignedUser = User::factory()->create(['suspended' => false]);
    $server = Server::factory()->create();
    $server->users()->attach($assignedUser);

    $viewer = User::factory()->create(['scopes' => ['server:view:*']]);

    actingAs($viewer);

    get(route('server.show', $server))
        ->assertOk()
        ->assertSee($assignedUser->name);
});

it('shows active check run indicator when run is in progress', function () {
    $user = User::factory()->create(['scopes' => ['server:view:*']]);
    $server = Server::factory()->create();

    // Simulate an active run
    session(["server_run.{$server->id}" => 'test-run-id']);

    actingAs($user);

    get(route('server.show', $server))
        ->assertOk()
        ->assertViewHas('activeRunId', 'test-run-id');
});

it('shows run completed indicator when run just finished', function () {
    $user = User::factory()->create(['scopes' => ['server:view:*']]);
    $server = Server::factory()->create(['last_check_run_id' => 'completed-run-id']);

    // Set session to indicate run was active
    session(["server_run.{$server->id}" => 'completed-run-id']);

    actingAs($user);

    get(route('server.show', $server))
        ->assertOk()
        ->assertViewHas('runCompleted', true)
        ->assertViewHas('activeRunId', null);

    // Session should be cleared
    expect(session("server_run.{$server->id}"))->toBeNull();
});
