<?php

use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;

uses(RefreshDatabase::class);

test('all routes are covered by authorization', function () {
    Server::factory()->create();
    // guest
    assertGuest();
    get('/users')->assertRedirectToRoute('login');
    get('/user')->assertNotFound();
    get('/user/randomid')->assertRedirectToRoute('login');
    get('/user/randomid/edit')->assertRedirectToRoute('login');
    patch('/user/randomid')->assertRedirectToRoute('login');
    delete('/user/randomid')->assertRedirectToRoute('login');

    // user
    $user = User::factory()->create();
    actingAs($user);
    get('/users')->assertForbidden();
    get('/user')->assertNotFound();
    get('/user/'.$user->id)->assertForbidden();
    get('/user/'.$user->id.'/edit')->assertForbidden();
    patch('/user/'.$user->id)->assertForbidden();
    delete('/user/'.$user->id)->assertForbidden();
});

test('users index can be displayed', function () {
    $userWithScope = User::factory()->create();
    $userWithScope->setScope(['user:view:*']);
    $userWithScope->save();
    $user = User::factory()->create();

    actingAs($userWithScope);
    get('/users')
        ->assertOk()
        ->assertSee($user->name);
});

test('user show can be displayed', function () {
    $userWithScope = User::factory()->create();
    $userWithScope->setScope(['user:view:*']);
    $userWithScope->save();
    $user = User::factory()->create();

    actingAs($userWithScope);
    get('/user/'.$user->id)
        ->assertOk()
        ->assertSee($user->name)
        ->assertSee($user->email);
});
