<?php

namespace App\Providers;

use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\Server::class => \App\Policies\ServerPolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class,
    ];
    /**
     * Register services.
     */
    public function register(): void
    {
    }

    /**
     * Register the application's gate definitions.
     */
    public static function boot(): void
    {
        Gate::define('config:manage', function (\App\Models\User $user): Response {
            return $user->has_scope('config:manage')
                ? Response::allow()
                : Response::deny('Sorry can\'t let you in.');
        });

        Gate::define('not-suspended', function (\App\Models\User $user): Response {
            /**
             * Check if the user is suspended.
             *
             * @return Response
             */
            return $user->is_suspended()
                ? Response::deny('Your account has been suspended.')
                : Response::allow();
        });
    }
}
