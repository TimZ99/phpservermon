<?php

use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function serverViewer(): User
{
    $user = User::factory()->create();
    $user->setScope(['server:view:*']);
    $user->save();

    return $user;
}

it('allows viewers to list servers and exposes status helpers', function () {
    $viewer = serverViewer();

    $success = Server::factory()->create(['overall_status' => 'success']);
    $warning = Server::factory()->create(['overall_status' => 'warning']);
    $danger = Server::factory()->create(['overall_status' => 'unknown']);

    actingAs($viewer)
        ->get(route('server.index'))
        ->assertOk()
        ->assertViewHas('servers', function ($servers) use ($success, $warning, $danger) {
            $map = $servers->keyBy('id');

            return $map[$success->id]->statusCss === 'success'
                && $map[$warning->id]->statusCss === 'warning'
                && $map[$danger->id]->statusCss === 'danger';
        });
});

it('forbids users without view scope from listing servers', function () {
    actingAs(User::factory()->create())
        ->get(route('server.index'))
        ->assertForbidden();
});
