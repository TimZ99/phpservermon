<?php

namespace App\Providers;

use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register the application's gate definitions.
     */
    public static function boot(): void
    {
        Gate::policy(\App\Models\Server::class, \App\Policies\ServerPolicy::class);
        Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);

        Gate::define('config:manage', function (\App\Models\User $user): Response {
            return $user->hasScope('config:manage')
                ? Response::allow()
                : Response::deny('Sorry can\'t let you in.');
        });

        Gate::define('not-suspended', function (\App\Models\User $user): Response {
            /**
             * Check if the user is suspended.
             *
             * @return Response
             */
            return $user->isSuspended()
                ? Response::deny('Your account has been suspended.')
                : Response::allow();
        });
    }
}
