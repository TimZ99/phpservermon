<?php

use App\Models\User;

// isAdmin function
it('can check if a user is an admin', function () {
    $user = User::factory()->create(['admin' => true]);
    expect($user->isAdmin())->toBeTrue();

    $nonAdminUser = User::factory()->create(['admin' => false]);
    expect($nonAdminUser->isAdmin())->toBeFalse();
});

// is_suspended function
it('can check if a user is suspended', function () {
    $user = User::factory()->create(['suspended' => true]);
    expect($user->is_suspended())->toBeTrue();

    $activeUser = User::factory()->create(['suspended' => false]);
    expect($activeUser->is_suspended())->toBeFalse();
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

// set_scopes function and getScopes function
it('can set and check scopes for a user', function () {
    $user = User::factory()->create();

    expect($user->has_scope('edit:server'))->toBeFalse();

    $user->set_scopes(['edit:server', 'invalid:server']);

    expect($user->has_scope('edit:server'))->toBeTrue();
    expect($user->has_scope('invalid:scope'))->toBeFalse();
});
