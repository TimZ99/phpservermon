<?php

use App\Models\User;
use App\Models\Server;

test('servers page is displayed', function () {
    $this->seed();
    
    $user = User::all()->first();

    $response = $this->actingAs($user)->get('/servers');
    $response->assertOk();

});