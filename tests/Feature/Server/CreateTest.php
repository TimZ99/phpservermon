<?php

use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

it('denies access to guests and unauthorized users', function () {
    $guest = User::factory()->create();

    get(route('server.create'))->assertRedirect(route('login'));
    post(route('server.store'), ['name' => 'Guest Server'])->assertRedirect(route('login'));

    expect(Server::where('name', 'Guest Server')->exists())->toBeFalse();

    actingAs($guest);
    get(route('server.create'))->assertForbidden();
    post(route('server.store'), ['name' => 'Unauthorized'])->assertForbidden();
});

it('allows server managers to create a server', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);
    $assignee = User::factory()->create();

    actingAs($manager);
    get(route('server.create'))
        ->assertOk();

    $response = post(route('server.store'), [
        'name' => 'New Server',
        'ip' => '192.168.1.50',
        'port' => 8080,
        'users' => [$assignee->id],
    ]);

    $server = Server::where('name', 'New Server')->first();
    expect($server)->not->toBeNull();

    $response->assertRedirect(route('server.show', $server));
    expect($server->users->pluck('id')->toArray())->toBe([$assignee->id]);
});

it('validates required fields during server creation', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);
    actingAs($manager);

    post(route('server.store'), [])->assertSessionHasErrors(['name']);
    post(route('server.store'), ['name' => '', 'ip' => ''])->assertSessionHasErrors(['name']);
});

it('rejects suspended or invalid users on create', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);
    $suspendedUser = User::factory()->create(['suspended' => true]);

    actingAs($manager);

    post(route('server.store'), [
        'name' => 'Invalid server',
        'users' => [$suspendedUser->id, 999],
    ])->assertSessionHasErrors(['users.1']);
});
