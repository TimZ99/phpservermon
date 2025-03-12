<?php

use App\Models\User;

test('admin can create user ', function () {
    $this->markTestIncomplete('Create and store controller is not implemented yet.');

    $user = User::factory()->create();
    $admin = User::factory()->create(['admin' => true]);

    $this->get('/user/create')->assertRedirectToRoute('login');
    $this->actingAs($user)->get('/user/create')->assertForbidden();
    $this->actingAs($admin)->get('/user/create')->assertOk();

    $this->actingAs($admin)->post('/user', ['name' => 'New User', 'email' => 'newuser@example.com'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/users');

    $this->assertDatabaseHas('users', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
    ]);
});
