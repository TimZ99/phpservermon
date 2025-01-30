<?php

use App\Models\User;

test('servers page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/servers');

    $response->assertOk();
});

test('servers require login', function () {
    $response = $this->get('/servers');
    $response->assertRedirectToRoute('login');
});