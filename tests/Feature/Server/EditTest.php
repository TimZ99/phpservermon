<?php

use App\Models\User;
use App\Models\Server;

test('server information can be updated', function () {
    $this->seed();

    $user = User::first();
    $server = Server::all()->last();

    $response = $this->actingAs($user)->patch('/server/'.$server->id.'/edit', ['name' => 'Test Server Name']);
    $response->assertSessionHasNoErrors()->assertRedirect('/server/'.$server->id);
    
    $server->refresh();
    $this->assertSame('Test Server Name', $server->name);
});

test('user is authorized', function () {
    $this->seed();

    $user = User::first();
    $user->admin = false;
    $server = Server::all()->last();
    $id = $server->id;

    $response = $this->actingAs($user)->patch('/server/'.$server->id.'/edit', ['name' => 'Test Server Name']);

    $response->assertForbidden();

});