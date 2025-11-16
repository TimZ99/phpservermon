<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

uses(RefreshDatabase::class);

test('cannot edit other users without user:manage:* scope', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    actingAs($user1);
    patch('/user/'.$user2->id, ['name' => 'New Name'])
        ->assertForbidden();

    expect($user2->fresh()->name)->not->toBe('New Name');
});

test('user can edit other users with the user:manage:* scope', function () {
    $userWithScope = User::factory()->create();
    $userWithScope->setScope(['user:manage:*']);
    $user = User::factory()->create();

    actingAs($userWithScope);
    patch('/user/'.$user->id, ['name' => 'New Name'])
        ->assertSessionHasNoErrors()
        ->assertRedirectToRoute('user.show', $user->id);

    expect($user->fresh()->name)->toBe('New Name');
});
