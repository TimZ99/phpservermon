<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
});

it('redirects verified users away from the verification prompt', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    actingAs($user)
        ->get(route('verification.notice'))
        ->assertRedirect(route('server.monitor'));
});

it('shows the verification view for unverified users', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    actingAs($user)
        ->get(route('verification.notice'))
        ->assertOk()
        ->assertViewIs('auth.verify-email');
});

it('sends a verification notification when requested', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect()
        ->assertSessionHas('status', 'verification-link-sent');

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('redirects verified users when requesting a new verification link', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect(route('server.monitor'));

    Notification::assertNothingSent();
});

it('marks the email as verified when visiting the signed link', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(30),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    actingAs($user)
        ->get($verificationUrl)
        ->assertRedirect(route('server.monitor').'?verified=1');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('simply redirects when the email is already verified', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(30),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    actingAs($user)
        ->get($verificationUrl)
        ->assertRedirect(route('server.monitor').'?verified=1');
});
