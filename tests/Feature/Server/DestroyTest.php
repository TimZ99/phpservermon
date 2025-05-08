<?php

use App\Models\Server;
use App\Models\User;

// check if guest cannot delete a server
test('guest cannot delete a server', function () {
    $server = Server::factory()->create();

    $this->assertGuest();
    $this->delete('/server/'.$server->id)
        ->assertRedirectToRoute('login');
    $this->assertDatabaseCount('servers', 1);
});

test('only user with server:delete scope can delete a server', function () {
    $user = User::factory()->create();
    $userWithScope = User::factory()->create();
    $userWithScope->setScope(['server:delete']);
    $server = Server::factory(3)->create()->last();

    // check if user with server:delete scope can delete a server
    $this->actingAs($userWithScope)->delete('/server/'.$server->id)
        ->assertRedirectToRoute('server.index')
        ->assertSessionHasNoErrors();
    $this->assertDatabaseCount('servers', 2)
        ->assertDatabaseMissing('servers', ['id' => $server->id]);

    // check if user without scope returns forbidden
    $this->actingAs($user)->delete('/server/'.$server->id)->assertNotFound();
    $this->assertDatabaseCount('servers', 2);
});
