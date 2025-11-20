<?php

use App\Casts\EncryptedCheckSettings;
use Illuminate\Database\Eloquent\Model;

class FakeServerModel extends Model
{
    protected $guarded = [];

    protected $table = 'servers';
}

it('encrypts sensitive values and decrypts them automatically', function () {
    $cast = new EncryptedCheckSettings;
    $model = new FakeServerModel;

    $value = [
        'Headers' => [
            'enabled' => true,
            'token' => 'secret-token',
            'input' => [
                'auth_header' => 'Bearer foo',
            ],
        ],
        'Latency' => [
            'enabled' => true,
            'input' => ['warning_ms' => 600],
        ],
    ];

    $stored = $cast->set($model, 'check_settings', $value, []);
    $this->assertIsString($stored);

    $decoded = json_decode($stored, true);
    expect($decoded['Headers']['token'])->toStartWith('ENC:');
    expect($decoded['Headers']['input']['auth_header'])->toStartWith('ENC:');
    expect($decoded['Latency']['input']['warning_ms'])->toBe(600);

    $retrieved = $cast->get($model, 'check_settings', $stored, []);
    expect($retrieved['Headers']['token'])->toBe('secret-token');
    expect($retrieved['Headers']['input']['auth_header'])->toBe('Bearer foo');
    expect($retrieved['Latency']['input']['warning_ms'])->toBe(600);
});

it('gracefully handles invalid payloads', function () {
    $cast = new EncryptedCheckSettings;
    $model = new FakeServerModel;

    $empty = $cast->get($model, 'check_settings', null, []);
    expect($empty)->toBe([]);

    $junk = $cast->get($model, 'check_settings', 'not-json', []);
    expect($junk)->toBe([]);

    $stored = json_encode(['token' => 'ENC:not-base64']);
    $retrieved = $cast->get($model, 'check_settings', $stored, []);
    expect($retrieved['token'])->toBe('ENC:not-base64');
});

it('accepts json strings and ignores already encrypted values', function () {
    $cast = new EncryptedCheckSettings;
    $model = new FakeServerModel;

    $value = json_encode(['api_token' => 'secret']);
    $stored = $cast->set($model, 'check_settings', $value, []);
    expect($stored)->toBeString();

    $encryptedValue = 'ENC:'.base64_encode('not-a-valid-cipher');
    $retrieved = $cast->get($model, 'check_settings', json_encode(['api_token' => $encryptedValue]), []);
    expect($retrieved['api_token'])->toBe($encryptedValue);
});
