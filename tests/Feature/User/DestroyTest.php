<?php

use App\Models\User;

test('user with delete:user scope can delete a user, but not the last one', function () {
    $userWithScope1 = User::factory()->create();
    $userWithScope1->set_scopes(['edit:user', 'delete:user']);
    $userWithScope2 = User::factory()->create();
    $userWithScope2->set_scopes(['edit:user', 'delete:user']);

    $this->assertDatabaseCount('users', 2);

    $this->actingAs($userWithScope1)
        ->delete('/user/'.$userWithScope2->id)
        ->assertSessionHasNoErrors()
        ->assertRedirectToRoute('user.index');

    $this->assertDatabaseCount('users', 1);
    $this->assertNull($userWithScope2->fresh());

    // prevent deleting the last user with edit:user scope
    $this->actingAs($userWithScope1)
        ->delete('/user/'.$userWithScope1->id)
        ->assertSessionHasErrors('edit:userdelete');
    $this->assertDatabaseCount('users', 1);
    $this->assertNotNull($userWithScope1->fresh());
});

test('user without delete:user scope cannot delete other users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $this->assertDatabaseCount('users', 2);

    $this->actingAs($user1)
        ->delete('/user/'.$user2->id)
        ->assertForbidden();

    $this->assertDatabaseCount('users', 2);
});
