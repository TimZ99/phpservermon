<?php

use App\Models\Server;
use App\Models\User;

test('server monitor page is displayed', function () {
    $user = User::factory()->create();

    $this->get('/monitor')->assertRedirectToRoute('login');
    $this->actingAs($user)->get('/monitor')->assertOk();
});

test('servers index page is displayed', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create(['admin' => true]);
    $server = Server::factory(10)->create()->last();

    $this->get('/servers')->assertRedirectToRoute('login');
    $this->actingAs($user)->get('/servers')->assertForbidden();
    $this->actingAs($admin)->get('/servers')->assertOk()->assertSee($server->name);
});

test('servers page require login', function () {
    $this->assertGuest();
    $response = $this->get('/servers');
    $response->assertRedirectToRoute('login');
});

test('server show can be displayed', function () {
    $admin = User::factory()->create(['admin' => true]);
    $server = Server::factory()->create();

    $this->actingAs($admin)
        ->get('/server/'.$server->id)
        ->assertOk()
        ->assertSee($server->name)
        ->assertSee($server->port)
        ->assertSee($server->ip)
        ->assertViewIs('server.show');
});
