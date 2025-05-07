<?php

use App\Models\Server;
use App\Models\User;

test('all routes are covered by authorization', function () {
    Server::factory()->create();
    // guest
    $this->assertGuest();
    $this->get('/users')->assertRedirectToRoute('login');
    $this->get('/user')->assertNotFound();
    $this->get('/user/randomid')->assertRedirectToRoute('login');
    $this->get('/user/randomid/edit')->assertRedirectToRoute('login');
    $this->patch('/user/randomid')->assertRedirectToRoute('login');
    $this->delete('/user/randomid')->assertRedirectToRoute('login');

    // user
    $user = User::factory()->create();
    $this->actingAs($user)->get('/users')->assertForbidden();
    $this->actingAs($user)->get('/user')->assertNotFound();
    $this->actingAs($user)->get('/user/'.$user->id)->assertForbidden();
    $this->actingAs($user)->get('/user/'.$user->id.'/edit')->assertForbidden();
    $this->actingAs($user)->patch('/user/'.$user->id)->assertForbidden();
    $this->actingAs($user)->delete('/user/'.$user->id)->assertForbidden();
});

test('users index can be displayed', function () {
    $userWithScope = User::factory()->create();
    $userWithScope->set_scopes(['user:view:all']);
    $user = User::factory()->create();

    $this->actingAs($userWithScope)
        ->get('/users')
        ->assertOk()
        ->assertSee($user->name);
});

test('user show can be displayed', function () {
    $userWithScope = User::factory()->create();
    $userWithScope->set_scopes(['user:view:all']);
    $user = User::factory()->create();

    $this->actingAs($userWithScope)
        ->get('/user/'.$user->id)
        ->assertOk()
        ->assertSee($user->name)
        ->assertSee($user->email);
});
