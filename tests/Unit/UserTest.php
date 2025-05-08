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
it('can check if a user is the last with user:manage:* scope', function () {
    $user = User::factory()->create();
    $user->setScope(['user:manage:*']);
    $user->save();
    expect($user->isLastPowerfulUser())->toBeTrue();

    $user1 = User::factory()->create();
    $user1->setScope(['user:manage:*']);
    $user1->save();
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

    expect($user->hasScope('config:manage'))->toBeFalse();

    $user->setScope(['config:manage', 'invalid:server']);
    $user->save();

    expect($user->hasScope('config:manage'))->toBeTrue();
    expect($user->hasScope('invalid:scope'))->toBeFalse();
});
