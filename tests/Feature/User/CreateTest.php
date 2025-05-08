<?php

use App\Models\User;

test('user with user:create scope can create user ', function () {
    $this->markTestIncomplete('Create and store controller is not implemented yet.');

    $user = User::factory()->create();
    $userWithScope = User::factory()->create();
    $userWithScope->setScope(['user:create']);

    $this->get('/user/create')->assertRedirectToRoute('login');
    $this->actingAs($user)->get('/user/create')->assertForbidden();
    $this->actingAs($userWithScope)->get('/user/create')->assertOk();

    $this->actingAs($userWithScope)->post('/user', ['name' => 'New User', 'email' => 'newuser@example.com'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/users');

    $this->assertDatabaseHas('users', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
    ]);
});
