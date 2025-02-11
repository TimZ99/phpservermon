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
        Gate::define('admin-only', function (): Response {
            /**
             * Check if the user is an admin.
             *
             * @return Response
             */
            return Auth::user()->isAdmin()
                ? Response::allow()
                : Response::deny('Sorry can\'t let you in.');
        });

        Gate::define('not-suspended', function (): Response {
            /**
             * Check if the user is suspended.
             *
             * @return Response
             */
            return Auth::user()->isSuspended()
                ? Response::deny('Your account has been suspended.')
                : Response::allow();
        });

        Gate::define('user-connected-to-server', function (\App\Models\User $user, \App\Models\Server $server): Response {
            /**
             * Check if the user is connected to the server.
             *
             * @param User $user
             * @param Server $server
             * @return Response
             */
            return $server->users()->where('id', Auth::user()->id)->exists()
                ? Response::allow()
                : Response::deny('Sorry can\'t let you in.');
        });
    }
}
