<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

test('users with view scope can list all users', function () {
    $viewer = User::factory()->create();
    $viewer->setScope(['user:view:*']);
    $viewer->save();

    actingAs($viewer)
        ->get(route('user.index'))
        ->assertOk()
        ->assertViewIs('user.index');
});

test('users without view scope cannot list users', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('user.index'))
        ->assertForbidden();
});

test('users with view scope can view specific users', function () {
    $viewer = User::factory()->create();
    $viewer->setScope(['user:view:*']);
    $viewer->save();
    $target = User::factory()->create();

    actingAs($viewer)
        ->get(route('user.show', $target))
        ->assertOk()
        ->assertViewIs('user.show');
});

test('managers can access the edit page', function () {
    $manager = User::factory()->create();
    $manager->setScope(['user:manage:*']);
    $manager->save();
    $target = User::factory()->create();

    actingAs($manager)
        ->get(route('user.edit', $target))
        ->assertOk()
        ->assertViewIs('user.edit');
});
