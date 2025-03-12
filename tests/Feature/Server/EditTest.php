<?php

use App\Models\Server;
use App\Models\User;

test('forbidden when non-admin user tries to edit server ', function () {
    $user = User::factory()->has(Server::factory())->create();

    $server = $user->servers()->first();

    // check for user with relationship to server
    $this->actingAs($user)->patch('/server/'.$server->id, ['name' => 'Test Server Name'])
        ->assertForbidden();

    // check for user without relationship to server
    $server = Server::factory()->create();
    $this->actingAs($user)->patch('/server/'.$server->id, ['name' => 'Test Server Name'])
        ->assertForbidden();

});

test('server information can be updated by admin', function () {
    $admin = User::factory()->create(['admin' => true]);
    $server = Server::factory()->create();

    $this->actingAs($admin)->patch('/server/'.$server->id, ['name' => 'Test Server Name'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/server/'.$server->id);

    $server->refresh();
    $this->assertSame('Test Server Name', $server->name);
});

test('edit page can be rendered', function () {
    $admin = User::factory()->has(Server::factory())->create(['admin' => true]);

    $response = $this->actingAs($admin)->get('/server/'.$admin->servers()->first()->id.'/edit');

    $response->assertStatus(200);

    $response->assertSee($admin->servers()->first()->name);
    $response->assertSee($admin->servers()->first()->ip);
});
