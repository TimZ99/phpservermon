<?php

use App\Models\Server;
use App\Models\User;
test('guest cannot edit a server', function () {
    $server = Server::factory()->create();
    
    $this->assertGuest();
    $this->patch('/server/' . $server->id, ['name' => 'Test Server Name'])
        ->assertRedirect('/login');
});

test('non-admin user cannot edit a server they own', function () {
    $user = User::factory()->has(Server::factory())->create();
    $server = $user->servers()->first();

    $this->actingAs($user)
        ->patch('/server/' . $server->id, ['name' => 'Test Server Name'])
        ->assertForbidden();
});

test('non-admin user cannot edit a server they do not own', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create();

    $this->actingAs($user)
        ->patch('/server/' . $server->id, ['name' => 'Test Server Name'])
        ->assertForbidden();
});

test('admin can update server information', function () {
    $admin = User::factory()->create(['admin' => true]);
    $server = Server::factory()->create();

    $this->actingAs($admin)
        ->patch('/server/' . $server->id, ['name' => 'Updated Server Name'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/server/' . $server->id);

    $server->refresh();
    $this->assertSame('Updated Server Name', $server->name);
});

test('admin can view the edit page for a server', function () {
    $admin = User::factory()->has(Server::factory())->create(['admin' => true]);
    $server = $admin->servers()->first();

    $response = $this->actingAs($admin)->get('/server/' . $server->id . '/edit');

    $response->assertOk()
        ->assertSee($server->name)
        ->assertSee($server->ip);
});
