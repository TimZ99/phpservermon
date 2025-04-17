<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class GateServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Register the application's gate definitions.
     */
    public static function boot(): void
    {
        Gate::define('admin-only', function ($user): Response {
            /**
             * Check if the user is an admin.
             *
             * @return Response
             */
            return $user->isAdmin()
                ? Response::allow()
                : Response::deny('Sorry can\'t let you in.');
        });

        Gate::define('not-suspended', function ($user): Response {
            /**
             * Check if the user is suspended.
             *
             * @return Response
             */
            return $user->isSuspended()
                ? Response::deny('Your account has been suspended.')
                : Response::allow();
        });

        Gate::define('user-connected-to-server', function (\App\Models\User $user, \App\Models\Server $server): Response {
            /**
             * Check if the user is connected to the server.
             *
             * @param  User  $user
             * @param  Server  $server
             * @return Response
             */
            return $server->users()->where('id', $user->id)->exists()
                ? Response::allow()
                : Response::deny('Sorry can\'t let you in.');
        });
    }
}
