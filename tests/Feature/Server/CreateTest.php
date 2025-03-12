<?php

use App\Models\User;

test('admin can create server ', function () {
    $this->markTestIncomplete('Create and store controller is not implemented yet.');

    $user = User::factory()->create();
    $admin = User::factory()->create(['admin' => true]);

    $this->get('/server/create')->assertRedirectToRoute('login');
    $this->actingAs($user)->get('/server/create')->assertForbidden();
    $this->actingAs($admin)->get('/server/create')->assertOk();

    $this->actingAs($admin)->post('/server', ['name' => 'New Server', 'ip' => '192.168.1.1'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/servers');

    $this->assertDatabaseHas('servers', [
        'name' => 'New Server',
        'ip' => '192.168.1.1',
    ]);
});
