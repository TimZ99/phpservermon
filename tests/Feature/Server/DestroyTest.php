<?php

use App\Models\User;
use App\Models\Server;

test('admin can delete a server', function () {
    $admin = User::factory()->create(['admin' => true]);
    $user = User::factory()->create();
    $server = Server::factory(10)->create()->last();

    $this->assertDatabaseCount('servers', 10);
    $response = $this->actingAs($user)->delete('/server/'.$server->id);
    $response->assertForbidden();
    $this->assertDatabaseCount('servers', 10);

    $response = $this->actingAs($admin)->delete('/server/'.$server->id);
    $this->assertDatabaseCount('servers', 9);
    $response->assertSessionHasNoErrors()->assertRedirect('/servers');
    $response = $this->actingAs($admin)->get('/server/'.$server->id);
    $response->assertNotFound();
});