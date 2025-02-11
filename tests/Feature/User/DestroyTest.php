<?php

use App\Models\User;

test('admin can be deleted, but cannot delete the last admin', function () {
    $admin1 = User::factory()->create(['admin' => true]);
    $admin2 = User::factory()->create(['admin' => true]);

    $this->assertDatabaseCount('users', 2);

    $this->actingAs($admin1)
        ->delete('/user/' . $admin2->id)
        ->assertSessionHasNoErrors()
        ->assertRedirectToRoute('user.index');

    $this->assertDatabaseCount('users', 1);
    $this->assertNull($admin2->fresh());

    // prevent deleting the last admin
    $this->actingAs($admin1)
        ->delete('/user/' . $admin1->id)
        ->assertSessionHasErrors('admindelete');
    $this->assertDatabaseCount('users', 1);
    $this->assertNotNull($admin1->fresh());
});

test('non-admin user cannot delete other users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $this->assertDatabaseCount('users', 2);

    $this->actingAs($user1)
        ->delete('/user/' . $user2->id)
        ->assertForbidden();

    $this->assertDatabaseCount('users', 2);
});