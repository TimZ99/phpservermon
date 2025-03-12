<?php

use App\Models\Server;
use App\Models\User;

test('admin can view server list', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create(['admin' => true]);
    $server = Server::factory(2)->create()->last();

    $this->get('/servers')->assertRedirectToRoute('login');
    $this->actingAs($user)->get('/servers')->assertForbidden();
    $this->actingAs($admin)->get('/servers')->assertOk()->assertSee($server->name);
});

test('user can view server', function () {
    $this->assertGuest();
    $response = $this->get('/servers');
    $response->assertRedirectToRoute('login');

    $user = User::factory()->has(Server::factory())->create();
    $server = $user->servers()->first();

    $this->actingAs($user)
        ->get('/server/'.$server->id)
        ->assertOk()
        ->assertSee($server->name)
        ->assertSee($server->port)
        ->assertSee($server->ip)
        ->assertViewIs('server.show');
});
