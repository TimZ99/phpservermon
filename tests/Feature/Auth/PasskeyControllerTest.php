<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyRegisterOptionsAction;
use Spatie\LaravelPasskeys\Actions\StorePasskeyAction;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\LaravelPasskeys\Models\Passkey;
use Symfony\Component\Uid\Uuid;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\TrustPath\EmptyTrustPath;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('passkeys.actions.generate_passkey_register_options', FakeGeneratePasskeyOptionsAction::class);
    config()->set('passkeys.actions.store_passkey', FakeStorePasskeyAction::class);
});

it('returns passkey options and stores session metadata', function () {
    $user = User::factory()->create();

    actingAs($user);

    postJson(route('passkeys.options'), ['name' => 'Work Laptop'])
        ->assertOk()
        ->assertJson(['options' => FakeGeneratePasskeyOptionsAction::$response]);

    expect(session()->get('passkey-registration-name'))->toBe('Work Laptop')
        ->and(session()->get('passkey-registration-options'))->toBe(FakeGeneratePasskeyOptionsAction::$response);
});

it('stores a passkey when options exist in the session', function () {
    $user = User::factory()->create();
    actingAs($user);

    session()->put('passkey-registration-options', 'fake-options');
    session()->put('passkey-registration-name', 'Phone Key');

    postJson(route('passkeys.store'), [
        'passkey' => fakePasskeyPayload('duplicate-token'),
    ])->assertOk()->assertJson(['status' => 'created']);

    expect($user->fresh()->passkeys)->toHaveCount(1)
        ->and(session()->get('status'))->toBe('passkey-added');
});

it('requires passkey options in the session before storing', function () {
    $user = User::factory()->create();
    actingAs($user);

    postJson(route('passkeys.store'), [
        'passkey' => fakePasskeyPayload(),
    ])->assertStatus(422)->assertJsonValidationErrors('passkey');
});

it('prevents deleting passkeys owned by other users', function () {
    [$owner, $other] = User::factory()->count(2)->create();

    $passkey = Passkey::factory()->create([
        'authenticatable_id' => $owner->getAuthIdentifier(),
        'name' => 'Owner Key',
    ]);

    actingAs($other)
        ->delete(route('passkeys.destroy', $passkey))
        ->assertForbidden();

    actingAs($owner)
        ->delete(route('passkeys.destroy', $passkey))
        ->assertRedirect(route('profile.edit'));

    expect(Passkey::count())->toBe(0);
});

function fakePasskeyPayload(string $rawBinary = 'raw-id'): string
{
    $rawId = rtrim(strtr(base64_encode($rawBinary), '+/', '-_'), '=');

    return json_encode([
        'id' => $rawId,
        'rawId' => $rawId,
        'response' => [
            'attestationObject' => 'fake',
            'clientDataJSON' => 'fake',
        ],
        'type' => 'public-key',
        'clientExtensionResults' => [],
    ]);
}

class FakeGeneratePasskeyOptionsAction extends GeneratePasskeyRegisterOptionsAction
{
    public static string $response = '{"challenge":"fake"}';

    public function execute(HasPasskeys $authenticatable, bool $asJson = true): string
    {
        return self::$response;
    }
}

class FakeStorePasskeyAction extends StorePasskeyAction
{
    public static bool $shouldThrow = false;

    public static function normalize(string $rawBinary): string
    {
        return mb_convert_encoding($rawBinary, 'UTF-8');
    }

    public function execute(
        HasPasskeys $authenticatable,
        string $passkeyJson,
        string $passkeyOptionsJson,
        string $hostName,
        array $additionalProperties = [],
    ): Passkey {
        if (self::$shouldThrow) {
            throw new \RuntimeException('store failed');
        }

        $payload = json_decode($passkeyJson, true) ?? [];
        $rawId = $payload['rawId'] ?? '';
        $binary = base64_decode(strtr($rawId, '-_', '+/'), true) ?: $rawId;
        $credentialId = self::normalize($binary);

        $source = \Webauthn\PublicKeyCredentialSource::create(
            $credentialId,
            PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
            [],
            'none',
            EmptyTrustPath::create(),
            Uuid::fromString('00000000-0000-0000-0000-000000000000'),
            $binary,
            'fake-user',
            0,
        );

        return $authenticatable->passkeys()->create([
            ...$additionalProperties,
            'data' => $source,
        ]);
    }
}
it('converts store action failures into validation errors', function () {
    $user = User::factory()->create();
    actingAs($user);

    session()->put('passkey-registration-options', 'fake-options');
    session()->put('passkey-registration-name', 'Phone Key');
    FakeStorePasskeyAction::$shouldThrow = true;

    postJson(route('passkeys.store'), [
        'passkey' => fakePasskeyPayload(),
    ])->assertStatus(422)->assertJsonValidationErrors('passkey');

    FakeStorePasskeyAction::$shouldThrow = false;
});
