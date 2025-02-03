<?php

use App\Models\User;
use App\Models\Server;

test('servers page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/servers');
    $response->assertOk();

});

test('servers page require login', function () {
    $response = $this->get('/servers');
    $response->assertRedirectToRoute('login');
});

test('server information can be updated', function () {
    $this->seed();
    $user = User::factory()->create();
    $server = Server::all()->last();

    $response = $this
        ->actingAs($user)
        ->patch('/server/'.$server->server_id.'/edit', ['name' => 'Test Server Name']);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/server/'.$server->server_id.'/edit');

    $server->refresh();

    $this->assertSame('Test Server Name', $server->name);
});

test('user is authorized to update server', function () {
    $this->seed();
    $user = User::factory()->create();
    $server = Server::all()->last();
    $oldServername = $server->name;

    $response = $this
        ->patch('/server/'.$server->server_id.'/edit', ['name' => 'Test Server Name']);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/login');

    $server->refresh();

    $this->assertSame($oldServername, $server->name);
});

test('server can be deleted', function () {
    $this->seed();
    $user = User::factory()->create();
    $server = Server::all()->last();

    $this->assertDatabaseCount('servers', 10);

    $response = $this
        ->actingAs($user)
        ->delete('/server/'.$server->server_id.'/edit');

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/servers');

    $this->assertDatabaseCount('servers', 9);
});

test('user is authorized to delete server', function () {
    $this->seed();
    $user = User::factory()->create();
    $server = Server::all()->last();
    $oldServername = $server->name;

    $response = $this
        ->delete('/server/'.$server->server_id.'/edit');

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/login');

    $this->assertDatabaseCount('servers', 10);
});