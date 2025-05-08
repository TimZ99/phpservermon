<?php

use App\Models\User;

test('cannot edit other users without user:manage:* scope', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $this->actingAs($user1)
        ->patch('/user/'.$user2->id, ['name' => 'New Name'])
        ->assertForbidden();

    $this->assertNotEquals('New Name', $user2->fresh()->name);
});

test('user can edit other users with the user:manage:* scope', function () {
    $userWithScope = User::factory()->create();
    $userWithScope->setScope(['user:manage:*']);
    $user = User::factory()->create();

    $this->actingAs($userWithScope)
        ->patch('/user/'.$user->id, ['name' => 'New Name'])
        ->assertSessionHasNoErrors()
        ->assertRedirectToRoute('user.show', $user->id);

    $this->assertEquals('New Name', $user->fresh()->name);
});
