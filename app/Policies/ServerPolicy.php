<?php

namespace App\Policies;

use App\Models\Server;
use App\Models\User;

class ServerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function monitor(User $user): bool
    {
        return $user->has_scope('server:monitor');
    }

    /**
     * Determine whether the user can view any models.
     */
    public function index(User $user): bool
    {
        return $user->has_scope('server:view:any');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Server $server): bool
    {
        return $user->has_any_scope([
            'server:view:any',
            'server:edit:any',
            'server:delete:any',
            'server:'.$server->uuid.':view',
            'server:'.$server->uuid.':edit',
            'server:'.$server->uuid.':delete',
            'server:'.$server->uuid.':check'
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function check(User $user, Server $server): bool
    {
        return $user->has_any_scope([
            'server:edit:any',
            'server:delete:any',
            'server:'.$server->uuid.':check',
            'server:'.$server->uuid.':edit',
            'server:'.$server->uuid.':delete'
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function checkAny(User $user): bool
    {
        return $user->has_scope('server:check:any');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->has_scope('server:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Server $server): bool
    {
        return $user->has_any_scope([
            'server:edit:any',
            'server:delete:any',
            'server:'.$server->uuid.':edit',
            'server:'.$server->uuid.':delete'
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Server $server): bool
    {
        return $user->has_any_scope([
            'server:'.$server->uuid.':edit',
            'server:'.$server->uuid.':delete'
        ]);
    }
}
