<?php

use App\Models\User;

test('admin can create server ', function () {
    $this->markTestIncomplete('Create and store controller is not implemented yet.');

    $user = User::factory()->create();
    $admin = User::factory()->create(['admin' => true]);

    // Test unauthenticated user
    $this->assertGuest();
    $this->get('/server/create')->assertRedirectToRoute('login');
    $this->post('/server', ['name' => 'Unauthorized Server', 'ip' => '10.0.0.1'])
        ->assertRedirectToRoute('login');
    $this->assertDatabaseMissing('servers', [
        'name' => 'Unauthorized Server',
        'ip' => '10.0.0.1',
    ]);

    // Test authenticated user
    $this->actingAs($user)->get('/server/create')->assertForbidden();
    $this->actingAs($user)->post('/server', ['name' => 'Unauthorized Server', 'ip' => '10.0.0.1'])
        ->assertForbidden();
    // Ensure no server is created in the database
    $this->assertDatabaseMissing('servers', [
        'name' => 'Unauthorized Server',
        'ip' => '10.0.0.1',
    ]);

    // Test admin user
    $this->actingAs($admin)->get('/server/create')->assertOk();
    $this->actingAs($admin)->post('/server', ['name' => 'New Server', 'ip' => '192.168.1.1'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/servers');

    $this->assertDatabaseHas('servers', [
        'name' => 'New Server',
        'ip' => '192.168.1.1',
    ]);
});
