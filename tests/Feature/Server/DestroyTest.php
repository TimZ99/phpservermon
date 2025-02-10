<?php

use App\Models\User;
use App\Models\Server;

test('only admin can delete server', function () {
    $this->seed();

    $user = User::first();
    $user->admin = false;
    $server = Server::all()->last();

    $this->assertDatabaseCount('servers', 10);
    $response = $this->actingAs($user)->delete('/server/'.$server->id);
    $response->assertForbidden();
    $this->assertDatabaseCount('servers', 10);
});

test('server can be deleted', function () {
    $this->seed();

    $user = User::first();
    $server = Server::all()->last();
    
    $this->assertDatabaseCount('servers', 10);
    $response = $this->actingAs($user)->delete('/server/'.$server->id);
    $this->assertDatabaseCount('servers', 9);
    $response->assertSessionHasNoErrors()->assertRedirect('/servers');
    $response = $this->actingAs($user)->get('/server/'.$server->id);
    $response->assertNotFound();
});