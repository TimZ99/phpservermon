<?php

use App\Models\NotificationPreference;
use App\Models\Server;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

// isSuspended function
it('can check if a user is suspended', function () {
    $this->user['suspended'] = true;
    expect($this->user->isSuspended())->toBeTrue();

    $this->user['suspended'] = false;
    expect($this->user->isSuspended())->toBeFalse();
});

// isLastPowerfulUser function
it('can check if a user is the last with user:manage:* scope', function () {
    $this->user->setScope(['user:manage:*']);
    $this->user->save();
    expect($this->user->isLastPowerfulUser())->toBeTrue();

    $user1 = User::factory()->create();
    $user1->setScope(['user:manage:*']);
    $user1->save();
    expect($user1->isLastPowerfulUser())->toBeFalse();
});

// routeNotificationForTelegram function
it('can route notifications for Telegram', function () {
    $this->user['telegram_user_id'] = 123456;
    expect($this->user->routeNotificationForTelegram())->toBe(123456);
});

it('returns null for telegram route when chat id missing', function () {
    $this->user['telegram_user_id'] = null;
    expect($this->user->routeNotificationForTelegram())->toBeNull();

    $this->user['telegram_user_id'] = '';
    expect($this->user->routeNotificationForTelegram())->toBeNull();
});

// setScope function and getScopes function
it('can set and check scopes for a user', function () {
    expect($this->user->hasScope('config:manage'))->toBeFalse();

    $this->user->setScope(['config:manage', 'invalid:server']);
    $this->user->save();

    expect($this->user->hasScope('config:manage'))->toBeTrue();
    expect($this->user->hasScope('invalid:scope'))->toBeFalse();
});

// addScope function
it('can add a scope to a user', function () {
    $this->user->addScope(['server:manage:*']);
    $this->user->save();
    expect($this->user->hasScope('server:manage:*'))->toBeTrue();

    $this->user->addScope(['config:manage', 'user:manage:*']);
    $this->user->save();

    expect($this->user->hasScope('server:manage:*'))->toBeTrue();
    expect($this->user->hasScope('config:manage'))->toBeTrue();
    expect($this->user->hasScope('user:manage:*'))->toBeTrue();

    expect(fn () => $this->user->addScope(['invalid:manage', 'user:manage:*']))->toThrow(InvalidArgumentException::class);
});

// removeScope function
it('can remove a scope from a user', function () {

    $this->user->addScope(['server:manage:*', 'config:manage', 'user:manage:*']);
    $this->user->save();

    $this->user->removeScope(['server:manage:*']);
    $this->user->save();

    expect($this->user->hasScope('server:manage:*'))->toBeFalse();

    $this->user->removeScope(['config:manage', 'user:manage:*']);
    $this->user->save();

    expect($this->user->hasScope('config:manage'))->toBeFalse();
    expect($this->user->hasScope('user:manage:*'))->toBeFalse();

    expect(fn () => $this->user->removeScope(['invalid:manage', 'user:manage:*']))->toThrow(InvalidArgumentException::class);
});

// hasScope
it('returns true when scope exists in array', function () {
    $this->user->scopes = ['config:manage', 'user:view'];
    expect($this->user->hasScope('config:manage'))->toBeTrue();
    expect($this->user->hasScope(['foo', 'user:view']))->toBeTrue();
});

it('returns false when scope does not exist in array', function () {
    $this->user->scopes = ['config:manage'];
    expect($this->user->hasScope('user:edit'))->toBeFalse();
});

it('returns true when scope exists in JSON string', function () {
    $this->user->scopes = json_encode(['user:edit', 'admin:manage']);
    expect($this->user->hasScope('admin:manage'))->toBeTrue();
    expect($this->user->hasScope(['foo', 'user:edit']))->toBeTrue();
});

it('returns false when invalid JSON or no matching scope', function () {
    $this->user->scopes = '{not:a-valid-json';
    expect($this->user->hasScope('anything'))->toBeFalse();

    $this->user->scopes = json_encode(['a:b']);
    expect($this->user->hasScope('x:y'))->toBeFalse();
});

it('returns notification preferences related to the user', function () {
    $server = Server::factory()->create();
    $preference = NotificationPreference::create([
        'user_id' => $this->user->id,
        'server_id' => $server->id,
        'check_name' => 'cpu',
        'channel' => 'mail',
        'enabled' => true,
    ]);

    NotificationPreference::create([
        'user_id' => User::factory()->create()->id,
        'server_id' => $server->id,
        'check_name' => 'disk',
        'channel' => 'mail',
        'enabled' => true,
    ]);

    $preferences = $this->user->fresh()->notificationPreferences;

    expect($preferences)->toHaveCount(1);
    expect($preferences->first()->is($preference))->toBeTrue();
});
