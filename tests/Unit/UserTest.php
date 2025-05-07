<?php

use App\Models\User;

// is_suspended function
it('can check if a user is suspended', function () {
    $user = User::factory()->create(['suspended' => true]);
    expect($user->is_suspended())->toBeTrue();

    $activeUser = User::factory()->create(['suspended' => false]);
    expect($activeUser->is_suspended())->toBeFalse();
});

// is_last_powerful_user function
it('can check if a user is the last with user:edit:any scope', function () {
    $user = User::factory()->create();
    $user->set_scopes(['user:edit:any']);
    expect($user->is_last_powerful_user())->toBeTrue();

    $user1 = User::factory()->create();
    $user1->set_scopes(['user:edit:any']);
    expect($user1->is_last_powerful_user())->toBeFalse();
});

// routeNotificationForTelegram function
it('can route notifications for Telegram', function () {
    $user = User::factory()->create(['telegram_user_id' => 123456]);
    expect($user->routeNotificationForTelegram())->toBe(123456);
});

// set_scopes function and getScopes function
it('can set and check scopes for a user', function () {
    $user = User::factory()->create();

    expect($user->has_scope('server:edit'))->toBeFalse();

    $user->set_scopes(['server:edit', 'invalid:server']);

    expect($user->has_scope('server:edit'))->toBeTrue();
    expect($user->has_scope('invalid:scope'))->toBeFalse();
});
