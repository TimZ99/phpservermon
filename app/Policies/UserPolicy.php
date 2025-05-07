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
        return $user->has_any_scope([
            'user:index',
            'user:view:any',
            'user:edit:any',
            'user:delete:any'
        ]);
    }

    /**
     * Determine whether the user can view any models.
     */
    public function view(user $user): bool
    {
        return $user->has_any_scope([
            'user:index',
            'user:view:any',
            'user:edit:any',
            'user:delete:any'
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
    public function update(user $user): bool
    {
        return $user->has_any_scope([
            'user:edit:any',
            'user:delete:any'
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(user $user): bool
    {
        return $user->has_any_scope([
            'user:edit:any',
            'user:delete:any'
        ]);
    }
}
