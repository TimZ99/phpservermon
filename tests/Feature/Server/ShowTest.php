<?php

use App\Models\Server;
use App\Models\User;

test('only admin can view server list', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create(['admin' => true]);
    $servers = Server::factory(2)->create();

    // Guest cannot access server list
    $this->get('/servers')->assertRedirectToRoute('login');

    // Regular user cannot access server list
    $this->actingAs($user)
        ->get('/servers')
        ->assertForbidden();

    // Admin can access server list and see server details
    $this->actingAs($admin)
        ->get('/servers')
        ->assertOk()
        ->assertSee($servers->last()->name);
});

test('user and admin can view server they are assigned to, guest cannot', function () {
    // Guest cannot access server details
    $server = Server::factory()->create()->first();
    $this->assertGuest();
    $this->get('/server/'.$server->id)->assertRedirectToRoute('login');

    $user = User::factory()->has(Server::factory())->create();
    $server = $user->servers()->first();

    // Assigned user can view server details
    $this->actingAs($user)
        ->get('/server/'.$server->id)
        ->assertOk()
        ->assertSee($server->name)
        ->assertSee($server->port)
        ->assertSee($server->ip)
        ->assertViewIs('server.show');
});
