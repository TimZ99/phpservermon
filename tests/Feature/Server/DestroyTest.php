<?php

use App\Models\Server;
use App\Models\User;

test('admin can delete a server', function () {
    $admin = User::factory()->create(['admin' => true]);
    $server = Server::factory(2)->create()->last();

    // check if admin can delete a server
    $response = $this->actingAs($admin)->delete('/server/'.$server->id);
    $this->assertDatabaseCount('servers', 1);
    $response->assertSessionHasNoErrors()->assertRedirect('/servers');
    $response = $this->actingAs($admin)->get('/server/'.$server->id);
    $response->assertNotFound();
});

test('non-admin cannot delete a server', function () {
    $user = User::factory()->create();
    $server = Server::factory(2)->create()->last();

    // check if non-admin returns forbidden
    $this->assertDatabaseCount('servers', 2);
    $response = $this->actingAs($user)->delete('/server/'.$server->id);
    $response->assertForbidden();
    $this->assertDatabaseCount('servers', 2);
});
