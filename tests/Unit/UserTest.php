<?php

use App\Models\User;

// isAdmin function
it('can check if a user is an admin', function () {
    $user = User::factory()->create(['admin' => true]);
    expect($user->isAdmin())->toBeTrue();

    $nonAdminUser = User::factory()->create(['admin' => false]);
    expect($nonAdminUser->isAdmin())->toBeFalse();
});

// isSuspended function
it('can check if a user is suspended', function () {
    $user = User::factory()->create(['suspended' => true]);
    expect($user->isSuspended())->toBeTrue();

    $activeUser = User::factory()->create(['suspended' => false]);
    expect($activeUser->isSuspended())->toBeFalse();
});

// isLastAdmin function
it('can check if a user is the last admin', function () {
    $adminUser = User::factory()->create(['admin' => true]);
    expect($adminUser->isLastAdmin())->toBeTrue();

    $anotherAdmin = User::factory()->create(['admin' => true]);
    expect($adminUser->isLastAdmin())->toBeFalse();
});

// routeNotificationForTelegram function
it('can route notifications for Telegram', function () {
    $user = User::factory()->create(['telegram_user_id' => 123456]);
    expect($user->routeNotificationForTelegram())->toBe(123456);
});

// setScopes function and getScopes function
it('can set and check scopes for a user', function () {
    $user = User::factory()->create();

    expect($user->hasScope('edit:server'))->toBeFalse();

    $user->setScopes(['edit:server', 'invalid:server']);

    expect($user->hasScope('edit:server'))->toBeTrue();
    expect($user->hasScope('invalid:scope'))->toBeFalse();
});
