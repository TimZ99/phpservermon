<?php

use App\Models\User;
use App\Notifications\Messages\DynamicNotification;
use App\Settings\NotificationSettings;
use Illuminate\Support\Facades\Notification;

it('displays profile page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/profile')
        ->assertOk();
});

it('updates profile information', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

it('keeps email verification when email unchanged', function () {
    $user = User::factory()->create();
    $currentVerifiedAt = $user->email_verified_at;

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertTrue($currentVerifiedAt == $user->refresh()->email_verified_at);
});

it('allows a user to delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

it('requires correct password to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});

it('redirects unauthenticated users to login', function () {
    $this->get('/profile')->assertRedirect(route('login'));
    $this->patch('/profile')->assertRedirect(route('login'));
    $this->delete('/profile')->assertRedirect(route('login'));
    $this->get(route('profile.test.telegram'))->assertRedirect(route('login'));
});

it('aborts telegram test when globally disabled', function () {
    Notification::fake();
    NotificationSettings::fake([
        'telegram_global_enabled' => false,
        'telegram_bot_token' => null,
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.test.telegram'))
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('status', 'telegram-disabled');

    Notification::assertNothingSent();
});

it('sends telegram test notification when enabled', function () {
    Notification::fake();
    NotificationSettings::fake([
        'telegram_global_enabled' => true,
        'telegram_bot_token' => 'token',
    ]);

    $user = User::factory()->create([
        'telegram_user_id' => 123456,
    ]);

    $this->actingAs($user)
        ->get(route('profile.test.telegram'))
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('status', 'telegram-test-sent');

    Notification::assertSentTo(
        $user,
        DynamicNotification::class,
        fn (DynamicNotification $notification) => $notification->notification_event === 'test_message'
    );
});

it('reports error when telegram notification fails', function () {
    NotificationSettings::fake([
        'telegram_global_enabled' => true,
        'telegram_bot_token' => 'token',
    ]);

    $user = User::factory()->create([
        'telegram_user_id' => 123456,
    ]);

    Notification::shouldReceive('send')
        ->once()
        ->andThrow(new \Exception('boom'));

    $this->actingAs($user)
        ->get(route('profile.test.telegram'))
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('status', 'Failed to send Telegram notification: boom');
});
