<?php
use App\Models\User;
use App\Models\Server;

test('servers index page is displayed', function () {
    $user = User::factory()->create();
    $server = Server::factory(10)->create()->last(); 

    $this->actingAs($user)->get('/servers')
        ->assertOk()
        ->assertSee($server->id);
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
        ->get('/server/' . $server->id)
        ->assertOk()
        ->assertSee($server->name)
        ->assertSee($server->port)
        ->assertSee($server->ip)
        ->assertViewIs('server.show');
});