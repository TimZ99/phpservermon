<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;

// Not-suspended gate
it('allows non-suspended to pass not-suspended gate', function () {

    $user = User::factory()->create(['suspended' => false]);
    $this->actingAs($user);
    $response = Gate::inspect('not-suspended');
    expect($response->allowed())->toBeTrue();
    expect($response->message())->toBeNull();

    $user = User::factory()->create(['suspended' => true]);
    $this->actingAs($user);
    $response = Gate::inspect('not-suspended');
    expect($response->denied())->toBeTrue();
    expect($response->message())->toBeString();
});
