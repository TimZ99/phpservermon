<?php

use App\Models\Server;
use App\Models\User;

// check if guest cannot delete a server
test('guest cannot delete a server', function () {
    $server = Server::factory()->create();

    $this->assertGuest();
    $this->delete('/server/' . $server->id)
        ->assertRedirectToRoute('login');
    $this->assertDatabaseCount('servers', 1);
});

test('only admin can delete a server', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create(['admin' => true]);
    $server = Server::factory(3)->create()->last();

    // check if admin can delete a server
    $this->actingAs($admin)->delete('/server/'.$server->id)
        ->assertRedirectToRoute('server.index')
        ->assertSessionHasNoErrors();
    $this->assertDatabaseCount('servers', 2)
        ->assertDatabaseMissing('servers', ['id' => $server->id]);

    // check if non-admin returns forbidden
    $this->actingAs($user)->delete('/server/'.$server->id)->assertNotFound();
    $this->assertDatabaseCount('servers', 2);
});
