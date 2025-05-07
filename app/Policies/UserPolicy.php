<?php

namespace App\Policies;

use App\Models\user;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function index(user $user): bool
    {
        return $user->has_scope('user:index');
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(user $user): bool
    {
        return $user->has_scope('user:view:any');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $other_user): bool
    {
        return $user->has_any_scope([
            'user:'.$other_user->id.':view',
            'user:'.$other_user->id.':edit',
            'user:'.$other_user->id.':delete'
        ]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(user $user): bool
    {
        return $user->has_scope('user:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(user $user, User $other_user): bool
    {
        return $user->has_any_scope([
            'user:'.$other_user->id.':edit',
            'user:'.$other_user->id.':delete'
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(user $user, User $other_user): bool
    {
        return $user->has_any_scope([
            'user:'.$other_user->id.':edit',
            'user:'.$other_user->id.':delete'
        ]);
    }
}
