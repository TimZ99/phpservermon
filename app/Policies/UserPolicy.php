<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $user, User $target): bool
    {
        return $user->hasScope('user:view:*')
            || $user->hasScope("user:view:{$target->id}");
    }

    public function viewAny(User $user): bool
    {
        return $user->hasScope('user:view:*')
        || $user->hasScope('user:manage:*');
    }

    public function manage(User $user, User $target): bool
    {
        return $user->hasScope('user:manage:*')
            || $user->hasScope("user:manage:{$target->id}");
    }

    public function manageAny(User $user): bool
    {
        return $user->hasScope('user:manage:*');
    }
}
