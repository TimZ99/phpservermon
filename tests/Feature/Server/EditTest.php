<?php

use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;

uses(RefreshDatabase::class);

it('denies edit access to guests', function () {
    $server = Server::factory()->create();

    get(route('server.edit', $server))
        ->assertRedirect(route('login'));

    patch(route('server.update', $server), ['name' => 'Test Server Name'])
        ->assertRedirect(route('login'));
});

it('denies edit access to users without server manage scope', function () {
    $user = User::factory()->has(Server::factory())->create();
    $serverConnectedToUser = $user->servers()->first();
    $server = Server::factory()->create();

    actingAs($user);

    get(route('server.edit', $serverConnectedToUser))
        ->assertForbidden();

    patch(route('server.update', $serverConnectedToUser), ['name' => 'Test Server Name'])
        ->assertForbidden();

    get(route('server.edit', $server))
        ->assertForbidden();

    patch(route('server.update', $server), ['name' => 'Test Server Name'])
        ->assertForbidden();
});

it('allows users with server:manage:* to view edit form', function () {
    $userWithScope = User::factory()->create(['scopes' => ['server:manage:*']]);
    $server = Server::factory()->create();

    actingAs($userWithScope);

    get(route('server.edit', $server))
        ->assertOk()
        ->assertSee($server->name)
        ->assertSee($server->ip)
        ->assertViewIs('server.edit');
});

it('allows users with server:manage:* to update server information', function () {
    $userWithScope = User::factory()->create(['scopes' => ['server:manage:*']]);
    $server = Server::factory()->create();

    actingAs($userWithScope);

    patch(route('server.update', $server), ['name' => 'Updated Server Name'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('server.show', $server));

    expect($server->fresh()->name)->toBe('Updated Server Name');
});

it('updates server with all valid fields', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);
    $server = Server::factory()->create();

    actingAs($manager);

    patch(route('server.update', $server), [
        'name' => 'Fully Updated Server',
        'ip' => '192.168.1.100',
        'port' => 8080,
        'check_settings' => [
            'StatusCode' => ['enabled' => true],
            'SSL_expiration' => ['enabled' => true, 'days' => 14],
        ],
    ])->assertSessionHasNoErrors()
        ->assertRedirect(route('server.show', $server));

    $server->refresh();

    expect($server)
        ->name->toBe('Fully Updated Server')
        ->ip->toBe('192.168.1.100')
        ->port->toBe(8080)
        ->and($server->check_settings['StatusCode']['enabled'])->toBeTrue()
        ->and($server->check_settings['SSL_expiration']['enabled'])->toBeTrue()
        ->and($server->check_settings['SSL_expiration']['input']['days'])->toBe(14);
});

it('updates server user assignments', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);
    $user1 = User::factory()->create(['suspended' => false]);
    $user2 = User::factory()->create(['suspended' => false]);
    $user3 = User::factory()->create(['suspended' => false]);

    $server = Server::factory()->create();
    $server->users()->attach([$user1->id]);

    actingAs($manager);

    patch(route('server.update', $server), [
        'name' => $server->name,
        'users' => [$user2->id, $user3->id],
    ])->assertSessionHasNoErrors();

    $server->refresh();

    expect($server->users->pluck('id')->sort()->values()->toArray())
        ->toBe([$user2->id, $user3->id]);
});

it('validates required fields on update', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);
    $server = Server::factory()->create();

    actingAs($manager);

    patch(route('server.update', $server), ['name' => ''])
        ->assertSessionHasErrors(['name']);
});

it('validates port range on update', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);
    $server = Server::factory()->create();

    actingAs($manager);

    patch(route('server.update', $server), [
        'name' => 'Test',
        'port' => 100000,
    ])->assertSessionHasErrors(['port']);

    patch(route('server.update', $server), [
        'name' => 'Test',
        'port' => -1,
    ])->assertSessionHasErrors(['port']);
});

it('validates check settings on update', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);
    $server = Server::factory()->create();

    actingAs($manager);

    patch(route('server.update', $server), [
        'name' => 'Test',
        'check_settings' => [
            'SSL_expiration' => ['enabled' => true, 'days' => 500],
        ],
    ])->assertSessionHasErrors(['check_settings.SSL_expiration.days']);
});

it('allows users with resource-specific scope to edit that server', function () {
    $server = Server::factory()->create();
    $user = User::factory()->create(['scopes' => ["server:manage:{$server->id}"]]);

    actingAs($user);

    get(route('server.edit', $server))
        ->assertOk();

    patch(route('server.update', $server), ['name' => 'Resource Specific Update'])
        ->assertSessionHasNoErrors();

    expect($server->fresh()->name)->toBe('Resource Specific Update');
});

it('denies users with resource-specific scope from editing other servers', function () {
    $server1 = Server::factory()->create();
    $server2 = Server::factory()->create();
    $user = User::factory()->create(['scopes' => ["server:manage:{$server1->id}"]]);

    actingAs($user);

    get(route('server.edit', $server2))
        ->assertForbidden();

    patch(route('server.update', $server2), ['name' => 'Unauthorized Update'])
        ->assertForbidden();
});
