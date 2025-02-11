<?php

use App\Models\User;
use App\Models\Server;

test('all routes are covered by authorization', function () {
    $server = Server::factory()->create();
    //guest
    $this->assertGuest();
    $this->get('/users')->assertRedirectToRoute('login');
    $this->get('/user')->assertNotFound();
    $this->get('/user/randomid')->assertRedirectToRoute('login');
    $this->get('/user/randomid/edit')->assertRedirectToRoute('login');
    $this->patch('/user/randomid/edit')->assertRedirectToRoute('login');
    $this->delete('/user/randomid')->assertRedirectToRoute('login');

    //user
    $user = User::factory()->create();
    $this->actingAs($user)->get('/users')->assertOk();
    $this->actingAs($user)->get('/user')->assertNotFound();
    $this->actingAs($user)->get('/user/' . $user->id)->assertOk();
    $this->actingAs($user)->get('/user/' . $user->id . '/edit')->assertForbidden();
    $this->actingAs($user)->patch('/user/' . $user->id . '/edit')->assertForbidden();
    $this->actingAs($user)->delete('/user/' . $user->id)->assertForbidden();

    //admin
    $admin = User::factory()->create(['admin' => true]);
    $this->actingAs($admin)->get('/users')->assertOk();
    $this->actingAs($admin)->get('/user')->assertNotFound();
    $this->actingAs($admin)->get('/user/' . $user->id)->assertOk();
    $this->actingAs($admin)->get('/user/' . $user->id . '/edit')->assertOk();
    $this->actingAs($admin)->patch('/user/' . $user->id . '/edit')->assertRedirectToRoute('user.show', ['user' => $user->id]);
    $this->actingAs($admin)->delete('/user/' . $user->id)->assertRedirectToRoute('user.index');
});

test('users index can be displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/users')
        ->assertOk()
        ->assertSee($user->name);
});

test('user show can be displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/user/' . $user->id)
        ->assertOk()
        ->assertSee($user->name)
        ->assertSee($user->email);
});