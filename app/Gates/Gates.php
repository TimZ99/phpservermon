<?php

namespace App\Gates;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\Response;

class Gates
{
    /**
     * Register the application's gate definitions.
     *
     * @return void
     */
    static function boot(): void
    {
        Gate::define('admin-only', function () {
            return Auth::user()->isAdmin()
                ? Response::allow()
                : Response::deny('Sorry can\'t let you in.');
        });
        
        Gate::define('not-suspended', function () {
            return Auth::user()->isSuspended()
                ? Response::deny('Your account has been suspended.')
                : Response::allow();
        });

        Gate::define('user-connected-to-server', function (\App\Models\User $user, \App\Models\Server $server) {
            return $server->users()->where('id', Auth::user()->id)->exists()
                ? Response::allow()
                : Response::deny('Sorry can\'t let you in.');
        });
    }
}