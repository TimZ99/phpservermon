<?php
use App\Models\User;
use App\Models\Server;

test('servers index page is displayed', function () {
    $this->seed();
    $user = User::first();
    $server = Server::all()->last();

    $view = $this->view('server.index', ['servers' => Server::all()])
        ->assertSee($server->id);

    $this->actingAs($user)->get('/servers')
        ->assertOk();

});

test('servers page require login', function () {
    $this->assertGuest();
    $response = $this->get('/servers');
    $response->assertRedirectToRoute('login');
});

test('server show can be displayed', function () {
    $this->seed();

    $user = User::first();
    $server = Server::all()->last();
    $this->actingAs($user)
        ->get('/server/' . $server->id)
        ->assertOk()
        ->assertSee($server->name)
        ->assertSee($server->port)
        ->assertSee($server->ip);
});