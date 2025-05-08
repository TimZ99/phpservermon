<?php

use App\Models\User;

test('user with user:manage scope can delete a user, but not the last one', function () {
    $userWithScope1 = User::factory()->create();
    $userWithScope1->setScope(['user:manage:*']);
    $userWithScope1->save();
    $userWithScope2 = User::factory()->create();
    $userWithScope2->setScope(['user:manage:*']);
    $userWithScope2->save();

    $this->assertDatabaseCount('users', 2);

    $this->actingAs($userWithScope1)
        ->delete('/user/'.$userWithScope2->id)
        ->assertSessionHasNoErrors()
        ->assertRedirectToRoute('user.index');

    $this->assertDatabaseCount('users', 1);
    $this->assertNull($userWithScope2->fresh());

    // prevent deleting the last user with user:manage:* scope
    $this->actingAs($userWithScope1)
        ->delete('/user/'.$userWithScope1->id)
        ->assertSessionHasErrors('user:editdelete');
    $this->assertDatabaseCount('users', 1);
    $this->assertNotNull($userWithScope1->fresh());
});

test('user without user:manage scope cannot delete other users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $this->assertDatabaseCount('users', 2);

    $this->actingAs($user1)
        ->delete('/user/'.$user2->id)
        ->assertForbidden();

    $this->assertDatabaseCount('users', 2);
});
