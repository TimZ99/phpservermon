<?php

use App\Http\Controllers\ServerController;

it('returns empty array when header requirements are not provided', function () {
    $controller = app(ServerController::class);
    $method = new \ReflectionMethod($controller, 'parseHeaderRequirements');
    $method->setAccessible(true);

    expect($method->invoke($controller, null))->toBe([]);
});

it('parses and trims header requirements', function () {
    $controller = app(ServerController::class);
    $method = new \ReflectionMethod($controller, 'parseHeaderRequirements');
    $method->setAccessible(true);

    $raw = "  X-Test: value  \n\nX-Empty:\nX-Token:   \nX-Exists\n";

    $result = $method->invoke($controller, $raw);

    expect($result)->toBe([
        'X-Test' => 'value',
        'X-Empty' => null,
        'X-Token' => null,
        'X-Exists' => null,
    ]);
});
