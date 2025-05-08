<?php

use App\Models\User;

// isSuspended function
it('can check if a user is suspended', function () {
    $user = User::factory()->create(['suspended' => true]);
    expect($user->isSuspended())->toBeTrue();

    $activeUser = User::factory()->create(['suspended' => false]);
    expect($activeUser->isSuspended())->toBeFalse();
});

// isLastPowerfulUser function
it('can check if a user is the last with user:edit:any scope', function () {
    $user = User::factory()->create();
    $user->setScope(['user:edit:any']);
    expect($user->isLastPowerfulUser())->toBeTrue();

    $user1 = User::factory()->create();
    $user1->setScope(['user:edit:any']);
    expect($user1->isLastPowerfulUser())->toBeFalse();
});

// routeNotificationForTelegram function
it('can route notifications for Telegram', function () {
    $user = User::factory()->create(['telegram_user_id' => 123456]);
    expect($user->routeNotificationForTelegram())->toBe(123456);
});

// setScope function and getScopes function
it('can set and check scopes for a user', function () {
    $user = User::factory()->create();

    expect($user->hasScope('server:edit'))->toBeFalse();

    $user->setScope(['server:edit', 'invalid:server']);

    expect($user->hasScope('server:edit'))->toBeTrue();
    expect($user->hasScope('invalid:scope'))->toBeFalse();
});
