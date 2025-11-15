<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

beforeEach(function () {
    $this->user = User::factory()->create(['email' => 'john@example.com']);
    $this->user->setScope(['user:manage:*']);
    $this->otherUser = User::factory()->create(['email' => 'jane@example.com']);
    actingAs($this->user);
});

// Profile
it('allows keeping the same email on profile update', function () {
    patch('/profile', ['name' => 'John Doe', 'email' => 'john@example.com'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');
});

it('allows a new user\'s email on profile update', function () {
    patch('/profile', ['name' => 'John Doe', 'email' => 'peter@example.com'])
        ->assertSessionHasNoErrors();
});

// Users
it('rejects an already used user\'s email on profile update', function () {
    patch('/profile', ['name' => 'John Doe', 'email' => 'jane@example.com'])
        ->assertSessionHasErrors(['email']);
});

it('allows unchanged email', function () {
    patch('/user/'.$this->otherUser->id, ['name' => 'New Name', 'email' => 'jane@example.com'])
        ->assertSessionHasNoErrors();
});

it('allows new email for other user', function () {
    patch('/user/'.$this->otherUser->id, ['name' => 'New Name', 'email' => 'peter@example.com'])
        ->assertSessionHasNoErrors();
});

it('allows admin to change own email', function () {
    patch('/user/'.$this->user->id, ['name' => 'New Name', 'email' => 'peter@example.com'])
        ->assertSessionHasNoErrors();
});

it('rejects duplicate email for other user', function () {
    patch('/user/'.$this->otherUser->id, ['name' => 'New Name', 'email' => 'john@example.com'])
        ->assertSessionHasErrors(['email']);
});
