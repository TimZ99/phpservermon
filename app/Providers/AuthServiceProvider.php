<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
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
        Gate::define('not-suspended', function ($user): Response {
            /**
             * Check if the user is suspended.
             *
             * @return Response
             */
            return $user->is_suspended()
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

        /**
         * Define a gate for each scope.
         * Use list of scopes from user model.
         *
         * @uses \App\Models\User;
         *
         * @example
         *
         *  @can('server:edit')
         *  <button>Edit Post</button>
         *
         *  @endcan
         *
         * @example if (auth()->user()->can('server:edit')) {}
         * @example
         * @example User::has_scope('server:edit')
         */
        foreach (\App\Models\User::valid_scopes() as $scope) {
            Gate::define($scope, fn ($user) => $user->has_scope($scope));
        }
    }
}
