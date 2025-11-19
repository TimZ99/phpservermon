<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

it('can check if a user is allowed to enter the page', function () {
    $user = User::factory()->create();
    actingAs($user);
    get(route('user.create'))->assertForbidden();

    $user->addScope('user:manage:*');
    actingAs($user);
    get(route('user.create'))->assertStatus(418);
});

test('user with user:create scope currently receives 404 for unimplemented store', function () {
    $user = User::factory()->create();
    $userWithScope = User::factory()->create();
    $userWithScope->setScope(['user:manage:*']);
    $userWithScope->save();

    get('/user/create')->assertRedirectToRoute('login');

    actingAs($user);
    get('/user/create')->assertForbidden();

    actingAs($userWithScope);
    get('/user/create')->assertStatus(418);
    post('/user', ['name' => 'New User', 'email' => 'newuser@example.com'])
        ->assertNotFound();

    expect(User::where('email', 'newuser@example.com')->exists())->toBeFalse();
});
