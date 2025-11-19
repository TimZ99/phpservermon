<?php

use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\from;
use function Pest\Laravel\patch;

uses(RefreshDatabase::class);

function managerUser(): User
{
    $user = User::factory()->create();
    $user->setScope(['user:manage:*']);
    $user->save();

    return $user;
}

test('cannot edit other users without user:manage:* scope', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    actingAs($user1);
    patch(route('user.update', $user2), ['name' => 'New Name'])
        ->assertForbidden();

    expect($user2->fresh()->name)->not->toBe('New Name');
});

test('user can edit other users with the user:manage:* scope', function () {
    $userWithScope = managerUser();
    $user = User::factory()->create();

    actingAs($userWithScope);
    patch(route('user.update', $user), ['name' => 'New Name', 'email' => $user->email, 'suspended' => false])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('user.show', $user));

    expect($user->fresh()->name)->toBe('New Name');
});

test('manager syncs server assignments when updating a user', function () {
    $manager = managerUser();
    $user = User::factory()->create(['email' => 'member@example.com']);
    $servers = Server::factory()->count(2)->create();

    actingAs($manager);
    patch(route('user.update', $user), [
        'name' => 'Updated Member',
        'email' => 'member@example.com',
        'servers' => [$servers[0]->id, $servers[1]->id],
        'scopes' => ['user:view:*'],
        'suspended' => false,
    ])->assertRedirect(route('user.show', $user));

    expect($user->fresh()->name)->toBe('Updated Member');
    expect($user->fresh()->servers->pluck('id')->toArray())->toBe($servers->pluck('id')->toArray());
    expect($user->fresh()->scopes)->toContain('user:view:*');
});

test('manager detaches servers when none are supplied', function () {
    $manager = managerUser();
    $user = User::factory()->create(['email' => 'member@example.com']);
    $servers = Server::factory()->count(2)->create();
    $user->servers()->sync($servers->pluck('id')->toArray());

    actingAs($manager);
    patch(route('user.update', $user), [
        'name' => 'Member',
        'email' => 'member@example.com',
        'suspended' => false,
        'scopes' => $user->scopes ?? [],
    ])->assertRedirect(route('user.show', $user));

    expect($user->fresh()->servers)->toBeEmpty();
});

test('cannot remove the last user with user:manage scope', function () {
    $manager = managerUser();

    actingAs($manager);
    from(route('user.edit', $manager))
        ->patch(route('user.update', $manager), [
            'name' => $manager->name,
            'email' => $manager->email,
            'suspended' => false,
            'scopes' => [],
        ])
        ->assertSessionHasErrors('lastuser:editscope');

    expect($manager->fresh()->hasScope('user:manage:*'))->toBeTrue();
});

test('shows error when update throws an exception', function () {
    $manager = managerUser();
    $target = User::factory()->create();

    $dispatcher = User::getEventDispatcher();
    User::flushEventListeners();
    User::saving(function () {
        throw new \RuntimeException('save failed');
    });

    try {
        actingAs($manager);
        from(route('user.edit', $target))
            ->patch(route('user.update', $target), [
                'name' => 'New Name',
                'email' => $target->email,
                'suspended' => false,
            ])
            ->assertSessionHasErrors('general');
    } finally {
        User::flushEventListeners();
        User::setEventDispatcher($dispatcher);
    }
});
