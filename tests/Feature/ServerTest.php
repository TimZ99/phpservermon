<?php

test('servers page is displayed', function () {
    $response = $this->get('/servers');

    $response->assertStatus(200);
});
