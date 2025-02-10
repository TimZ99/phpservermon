<?php
use App\Models\User;
use App\Models\Server;

test('servers page is displayed', function () {
    $this->seed();

    $user = User::first();
    $server = Server::all()->last();

    $view = $this->view('server.index', ['servers' => Server::all()]);
 
    $view->assertSee($server->id);

    $response = $this->actingAs($user)->get('/servers');
    $response->assertOk();

});

test('servers page require login', function () {
    $this->assertGuest();
    $response = $this->get('/servers');
    $response->assertRedirectToRoute('login');
});