<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can check if a user is allowed to enter the page', function () {
    $this->actingAs($this->user)->get(route('user.create'))->assertForbidden();

    $this->user->addScope('user:manage:*');
    $this->actingAs($this->user)->get(route('user.create'))->assertStatus(418);
});

test('user with user:create scope can create user ', function () {
    $this->markTestIncomplete('Create and store controller is not implemented yet.');

    $user = User::factory()->create();
    $userWithScope = User::factory()->create();
    $userWithScope->setScope(['user:manage:*']);
    $userWithScope->save();

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
