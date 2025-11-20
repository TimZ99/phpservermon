<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;

uses(RefreshDatabase::class);

test('user with user:manage scope can delete a user, but not the last one', function () {
    $userWithScope1 = User::factory()->create();
    $userWithScope1->setScope(['user:manage:*']);
    $userWithScope1->save();
    $userWithScope2 = User::factory()->create();
    $userWithScope2->setScope(['user:manage:*']);
    $userWithScope2->save();

    expect(User::count())->toBe(2);

    actingAs($userWithScope1);
    delete('/user/'.$userWithScope2->id)
        ->assertSessionHasNoErrors()
        ->assertRedirectToRoute('user.index');

    expect(User::count())->toBe(1);
    expect($userWithScope2->fresh())->toBeNull();

    // prevent deleting the last user with user:manage:* scope
    actingAs($userWithScope1);
    delete('/user/'.$userWithScope1->id)
        ->assertSessionHasErrors('user:editdelete');
    expect(User::count())->toBe(1);
    expect($userWithScope1->fresh())->not->toBeNull();
});

test('user without user:manage scope cannot delete other users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    expect(User::count())->toBe(2);

    actingAs($user1);
    delete('/user/'.$user2->id)
        ->assertForbidden();
    expect(User::count())->toBe(2);
});
