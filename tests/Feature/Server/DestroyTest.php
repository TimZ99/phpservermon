<?php

use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;

uses(RefreshDatabase::class);

it('denies delete access to guests', function () {
    $server = Server::factory()->create();

    delete(route('server.destroy', $server))
        ->assertRedirect(route('login'));

    expect(Server::count())->toBe(1);
});

it('denies delete access to users without server:manage:* scope', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create();

    actingAs($user);

    delete(route('server.destroy', $server))
        ->assertForbidden();

    expect(Server::count())->toBe(1);
});

it('allows users with server:manage:* to delete servers', function () {
    $userWithScope = User::factory()->create(['scopes' => ['server:manage:*']]);
    $servers = Server::factory(3)->create();
    $serverToDelete = $servers->last();

    actingAs($userWithScope);

    delete(route('server.destroy', $serverToDelete))
        ->assertRedirect(route('server.index'))
        ->assertSessionHasNoErrors();

    expect(Server::count())->toBe(2)
        ->and(Server::find($serverToDelete->id))->toBeNull();
});

it('removes server-user associations when deleting server', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);
    $assignedUser = User::factory()->create();
    $server = Server::factory()->create();
    $server->users()->attach($assignedUser);

    actingAs($manager);

    expect($server->users)->toHaveCount(1);

    delete(route('server.destroy', $server))
        ->assertRedirect(route('server.index'));

    expect(Server::find($server->id))->toBeNull()
        ->and(\DB::table('server_user')->where('server_id', $server->id)->count())->toBe(0);
});

it('allows users with resource-specific scope to delete that server', function () {
    $server = Server::factory()->create();
    $user = User::factory()->create(['scopes' => ["server:manage:{$server->id}"]]);

    actingAs($user);

    delete(route('server.destroy', $server))
        ->assertRedirect(route('server.index'));

    expect(Server::find($server->id))->toBeNull();
});

it('denies users with resource-specific scope from deleting other servers', function () {
    $server1 = Server::factory()->create();
    $server2 = Server::factory()->create();
    $user = User::factory()->create(['scopes' => ["server:manage:{$server1->id}"]]);

    actingAs($user);

    delete(route('server.destroy', $server2))
        ->assertForbidden();

    expect(Server::find($server2->id))->not->toBeNull();
});

it('returns 404 when deleting non-existent server', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);

    actingAs($manager);

    delete(route('server.destroy', 99999))
        ->assertNotFound();
});
