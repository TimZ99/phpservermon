<?php

namespace App\Policies;

use App\Models\Server;
use App\Models\User;

class ServerPolicy
{
    public function view(User $user, Server $server): bool
    {
        return $user->hasScope('server:view:*')
            || $user->hasScope("server:view:{$server->id}")
            || $user->servers->contains($server);
    }

    public function viewAny(User $user): bool
    {
        return $user->hasScope('server:view:*')
            || $user->hasScope('server:manage:*');
    }

    public function manage(User $user, Server $server): bool
    {
        return $user->hasScope('server:manage:*')
            || $user->hasScope("server:manage:{$server->id}");
    }

    public function manageAny(User $user): bool
    {
        return $user->hasScope('server:manage:*');
    }

    public function check(User $user, Server $server): bool
    {
        return $this->manage($user, $server) // check is included with edit
            || $user->hasScope('server:check:*')
            || $user->hasScope("server:check:{$server->id}");
    }

    public function checkAny(User $user): bool
    {
        return $user->hasScope('server:check:*')
            || $user->hasScope('server:manage:*');
    }
}
