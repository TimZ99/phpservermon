<?php

use App\Models\User;

test('non-admin user cannot edit other users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $this->assertDatabaseCount('users', 2);

    $this->actingAs($user1)
        ->patch('/user/' . $user2->id . '/edit', ['name' => 'New Name'])
        ->assertForbidden();
    $this->assertDatabaseCount('users', 2);

    $this->assertNotEquals('New Name', $user2->fresh()->name);
});

test('admin user can edit other users', function () {
    $admin = User::factory()->create(['admin' => true]);
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->patch('/user/' . $user->id . '/edit', ['name' => 'New Name'])
        ->assertSessionHasNoErrors()
        ->assertRedirectToRoute('user.show', $user->id);

    $this->assertEquals('New Name', $user->fresh()->name);
});

test('user can be made admin or be suspended', function () {
    $admin = User::factory()->create(['admin' => true]);
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->patch('/user/' . $user->id . '/edit', ['admin' => true, 'suspended' => true])
        ->assertSessionHasNoErrors()
        ->assertRedirectToRoute('user.show', $user->id);

    $this->assertEquals(true, $user->fresh()->admin);
    $this->assertEquals(true, $user->fresh()->suspended);

    $this->actingAs($user)
        ->get('/servers')
        ->assertForbidden();
});
