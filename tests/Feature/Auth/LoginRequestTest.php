<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

use function Pest\Laravel\post;

uses(RefreshDatabase::class);

it('authenticates and redirects with valid credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('secret-pass')]);

    post(route('login'), [
        'email' => $user->email,
        'password' => 'secret-pass',
    ])
        ->assertRedirect(route('server.monitor'));

    expect(auth()->id())->toBe($user->id);
});

it('fails authentication and records a rate-limit hit with invalid credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('secret-pass')]);
    $key = Str::lower($user->email).'|127.0.0.1';
    RateLimiter::clear($key);

    post(route('login'), [
        'email' => $user->email,
        'password' => 'wrong-pass',
    ])->assertSessionHasErrors('email');

    expect(RateLimiter::attempts($key))->toBe(1);
});

it('throttles login requests after too many attempts', function () {
    $user = User::factory()->create();
    $key = Str::lower($user->email).'|127.0.0.1';

    RateLimiter::clear($key);
    foreach (range(1, 5) as $i) {
        RateLimiter::hit($key);
    }

    post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');
});
