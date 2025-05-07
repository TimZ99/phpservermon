<?php

use App\Models\Server;
use App\Models\User;

test('guest cannot edit a server', function () {
    $server = Server::factory()->create();

    $this->assertGuest();
    $this->patch('/server/'.$server->id, ['name' => 'Test Server Name'])
        ->assertRedirect('/login');
});

test('user without user:edit:all scope cannot edit servers', function () {
    $user = User::factory()->has(Server::factory())->create();
    $serverConnectedToUser = $user->servers()->first();
    $server = Server::factory()->create();

    $this->actingAs($user)
        ->get('/server/'.$serverConnectedToUser->id.'/edit')
        ->assertForbidden();
    $this->actingAs($user)
        ->patch('/server/'.$serverConnectedToUser->id, ['name' => 'Test Server Name'])
        ->assertForbidden();

    $this->actingAs($user)
        ->get('/server/'.$server->id.'/edit')
        ->assertForbidden();
    $this->actingAs($user)
        ->patch('/server/'.$server->id, ['name' => 'Test Server Name'])
        ->assertForbidden();
});

test('user with user:edit:all can update server information', function () {
    $userWithScope = User::factory()->create();
    $userWithScope->set_scopes(['server:edit']);
    $server = Server::factory()->create();

    $this->actingAs($userWithScope)->get('/server/'.$server->id.'/edit')
        ->assertOk()
        ->assertSee($server->name)
        ->assertSee($server->ip);

    $this->actingAs($userWithScope)
        ->patch('/server/'.$server->id, ['name' => 'Updated Server Name'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/server/'.$server->id);

    $server->refresh();
    $this->assertSame('Updated Server Name', $server->name);
});
