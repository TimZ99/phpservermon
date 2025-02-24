<?php

use App\Models\Server;
use App\Models\User;

test('user is authorized', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create();

    $this->actingAs($user)->patch('/server/'.$server->id, ['name' => 'Test Server Name'])
        ->assertForbidden();

});

test('server information can be updated', function () {
    $user = User::factory()->create(['admin' => true]);
    $server = Server::factory()->create();

    $response = $this->actingAs($user)->patch('/server/'.$server->id, ['name' => 'Test Server Name']);
    $response->assertSessionHasNoErrors()->assertRedirect('/server/'.$server->id);

    $server->refresh();
    $this->assertSame('Test Server Name', $server->name);
});
