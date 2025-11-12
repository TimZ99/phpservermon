<?php

use App\Services\ServerChecks\ServerCheckRunStore;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::clear();
});

it('stores and retrieves payloads for a run', function () {
    $store = new ServerCheckRunStore;
    $payload = ['foo' => 'bar'];

    $store->put('run-1', $payload);

    expect($store->get('run-1'))->toBe($payload);
});

it('forgets run payloads', function () {
    $store = new ServerCheckRunStore;
    $store->put('run-2', ['foo' => 'baz']);

    $store->forget('run-2');

    expect($store->get('run-2'))->toBe([]);
});
