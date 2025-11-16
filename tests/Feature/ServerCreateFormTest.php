<?php

use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

it('allows server managers to view the create form and create a server', function () {
    $manager = User::factory()->create();
    $manager->forceFill(['scopes' => ['server:manage:*']])->save();
    $assignee = User::factory()->create(['suspended' => false]);

    actingAs($manager);

    get(route('server.create'))
        ->assertOk()
        ->assertSee(__('Server settings'))
        ->assertSee(__('Check settings'))
        ->assertSee($assignee->name);

    $response = post(route('server.store'), [
        'name' => 'Managed server',
        'ip' => '192.0.2.1',
        'port' => 443,
        'users' => [$assignee->id],
    ]);

    $server = Server::where('name', 'Managed server')->first();

    expect($server)->not->toBeNull();

    $response->assertRedirect(route('server.show', $server));
    expect($server->users->pluck('id')->toArray())->toContain($assignee->id);
});

it('excludes suspended users from create form user list', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);
    $activeUser = User::factory()->create(['suspended' => false]);
    $suspendedUser = User::factory()->create(['suspended' => true]);

    actingAs($manager);

    get(route('server.create'))
        ->assertOk()
        ->assertSee($activeUser->name)
        ->assertDontSee($suspendedUser->name);
});

it('creates a server with users and check settings', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);
    $assignee = User::factory()->create(['suspended' => false]);

    actingAs($manager);

    $payload = [
        'name' => 'New server',
        'ip' => '127.0.0.1',
        'port' => 80,
        'users' => [$assignee->id],
        'check_settings' => [
            'StatusCode' => ['enabled' => true],
        ],
    ];

    $response = post(route('server.store'), $payload);

    $server = Server::where('name', 'New server')->first();
    expect($server)->not->toBeNull();

    $response->assertRedirect(route('server.show', $server));
    expect($server->users->pluck('id')->toArray())->toContain($assignee->id);
});

it('creates a server without users', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);

    actingAs($manager);

    post(route('server.store'), [
        'name' => 'Server without users',
        'ip' => 'example.com',
        'port' => 443,
    ])->assertRedirect();

    $server = Server::where('name', 'Server without users')->first();

    expect($server)->not->toBeNull()
        ->and($server->users)->toHaveCount(0);
});

it('creates a server with multiple users', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);
    $users = User::factory()->count(3)->create(['suspended' => false]);

    actingAs($manager);

    post(route('server.store'), [
        'name' => 'Multi-user server',
        'ip' => '10.0.0.1',
        'port' => 8080,
        'users' => $users->pluck('id')->toArray(),
    ])->assertRedirect();

    $server = Server::where('name', 'Multi-user server')->first();

    expect($server->users)->toHaveCount(3)
        ->and($server->users->pluck('id')->sort()->values()->toArray())
        ->toBe($users->pluck('id')->sort()->values()->toArray());
});

it('stores check settings correctly', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);

    actingAs($manager);

    post(route('server.store'), [
        'name' => 'Server with checks',
        'ip' => 'test.example.com',
        'check_settings' => [
            'StatusCode' => ['enabled' => true],
            'SSL_expiration' => ['enabled' => true, 'days' => 30],
            'ContentRegex' => ['enabled' => true, 'pattern' => '^OK$'],
            'Latency' => ['enabled' => true, 'warning_ms' => 500, 'fail_ms' => 2000],
        ],
    ])->assertRedirect();

    $server = Server::where('name', 'Server with checks')->first();

    expect($server->check_settings)
        ->toBeArray()
        ->and($server->check_settings['StatusCode']['enabled'])->toBeTrue()
        ->and($server->check_settings['SSL_expiration']['enabled'])->toBeTrue()
        ->and($server->check_settings['SSL_expiration']['input']['days'])->toBe(30)
        ->and($server->check_settings['ContentRegex']['enabled'])->toBeTrue()
        ->and($server->check_settings['ContentRegex']['input']['pattern'])->toBe('^OK$')
        ->and($server->check_settings['Latency']['enabled'])->toBeTrue();
});

it('validates required fields', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);

    actingAs($manager);

    post(route('server.store'), [])
        ->assertSessionHasErrors(['name']);

    post(route('server.store'), ['name' => ''])
        ->assertSessionHasErrors(['name']);
});

it('validates port number range', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);

    actingAs($manager);

    post(route('server.store'), [
        'name' => 'Test Server',
        'ip' => '127.0.0.1',
        'port' => 100000, // Over max
    ])->assertSessionHasErrors(['port']);

    post(route('server.store'), [
        'name' => 'Test Server',
        'ip' => '127.0.0.1',
        'port' => -1, // Below min
    ])->assertSessionHasErrors(['port']);
});

it('validates check settings fields', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);

    actingAs($manager);

    post(route('server.store'), [
        'name' => 'Test Server',
        'check_settings' => [
            'SSL_expiration' => ['enabled' => true, 'days' => 500], // Over max
        ],
    ])->assertSessionHasErrors(['check_settings.SSL_expiration.days']);

    post(route('server.store'), [
        'name' => 'Test Server',
        'check_settings' => [
            'Latency' => ['enabled' => true, 'warning_ms' => 200000], // Over max
        ],
    ])->assertSessionHasErrors(['check_settings.Latency.warning_ms']);
});

it('updates an existing server and syncs users', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);
    $initialUsers = User::factory()->count(2)->create(['suspended' => false]);
    $server = Server::factory()->create([
        'name' => 'Original Server',
        'ip' => '198.51.100.1',
        'port' => 8080,
        'check_settings' => [
            'StatusCode' => ['enabled' => true],
        ],
    ]);
    $server->users()->sync($initialUsers->pluck('id')->toArray());
    $newAssignee = User::factory()->create(['suspended' => false]);

    actingAs($manager);

    patch(route('server.update', $server), [
        'name' => 'Updated Server',
        'ip' => '203.0.113.10',
        'port' => 8443,
        'users' => [$newAssignee->id],
        'check_settings' => [
            'StatusCode' => ['enabled' => false],
            'SSL_expiration' => ['enabled' => true, 'days' => 14],
        ],
    ])->assertRedirect(route('server.show', $server));

    $server->refresh();

    expect($server->name)->toBe('Updated Server')
        ->and($server->ip)->toBe('203.0.113.10')
        ->and($server->port)->toBe(8443)
        ->and($server->users->pluck('id')->toArray())->toBe([$newAssignee->id])
        ->and($server->check_settings['StatusCode']['enabled'])->toBeFalse()
        ->and($server->check_settings['SSL_expiration']['enabled'])->toBeTrue()
        ->and($server->check_settings['SSL_expiration']['input']['days'])->toBe(14);
});

it('detaches users when none are provided on update', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);
    $server = Server::factory()->create();
    $server->users()->sync(User::factory()->count(2)->create()->pluck('id'));

    actingAs($manager);

    patch(route('server.update', $server), [
        'name' => $server->name,
        'ip' => $server->ip,
        'port' => $server->port,
    ])->assertRedirect(route('server.show', $server));

    expect($server->fresh()->users)->toHaveCount(0);
});

it('validates that selected users exist', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);

    actingAs($manager);

    post(route('server.store'), [
        'name' => 'Server with invalid user',
        'ip' => '10.0.0.5',
        'users' => [999],
    ])->assertSessionHasErrors(['users.0']);
});

it('denies access to users without server manage scope', function () {
    actingAs(User::factory()->create());

    get(route('server.create'))->assertForbidden();

    post(route('server.store'), [
        'name' => 'Unauthorized Server',
        'ip' => '10.0.0.1',
    ])->assertForbidden();
});

it('denies access to unauthenticated users', function () {
    get(route('server.create'))->assertRedirect(route('login'));

    post(route('server.store'), [
        'name' => 'Unauthorized Server',
        'ip' => '10.0.0.1',
    ])->assertRedirect(route('login'));
});

it('accepts valid port numbers', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);

    actingAs($manager);

    // Test common ports
    foreach ([80, 443, 8080, 3000, 22, 21, 25] as $port) {
        post(route('server.store'), [
            'name' => "Server on port {$port}",
            'ip' => '127.0.0.1',
            'port' => $port,
        ])->assertSessionHasNoErrors();
    }

    expect(Server::count())->toBe(7);
});

it('accepts null port', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);

    actingAs($manager);

    post(route('server.store'), [
        'name' => 'Server without port',
        'ip' => 'example.com',
        'port' => null,
    ])->assertSessionHasNoErrors();

    expect(Server::where('name', 'Server without port')->first()->port)->toBeNull();
});

it('creates server with only name required', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);

    actingAs($manager);

    post(route('server.store'), [
        'name' => 'Minimal Server',
        'ip' => '127.0.0.1', // IP is required by database schema
    ])->assertSessionHasNoErrors();

    $server = Server::where('name', 'Minimal Server')->first();

    expect($server)->not->toBeNull()
        ->and($server->ip)->toBe('127.0.0.1')
        ->and($server->port)->toBeNull();
});

it('trims whitespace from server name', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);

    actingAs($manager);

    post(route('server.store'), [
        'name' => '  Server with spaces  ',
        'ip' => '127.0.0.1',
    ])->assertSessionHasNoErrors();

    // Laravel validation automatically trims strings
    expect(Server::where('name', 'Server with spaces')->exists())->toBeTrue();
});

it('merges check settings with defaults when partially specified', function () {
    $manager = User::factory()->create(['scopes' => ['server:manage:*']]);

    actingAs($manager);

    post(route('server.store'), [
        'name' => 'Partial checks',
        'ip' => '10.0.0.1',
        'check_settings' => [
            'StatusCode' => ['enabled' => true],
            // Other checks should get default values
        ],
    ])->assertSessionHasNoErrors();

    $server = Server::where('name', 'Partial checks')->first();

    // Should have all check definitions with defaults
    expect($server->check_settings)->toBeArray()
        ->and($server->check_settings['StatusCode']['enabled'])->toBeTrue()
        ->and($server->check_settings)->toHaveKey('SSL_expiration')
        ->and($server->check_settings)->toHaveKey('ContentRegex');
});
