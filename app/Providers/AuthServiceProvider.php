<?php

namespace App\Providers;

use App\Models\Server;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
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
         *  @endcan
         *
         * @example if (auth()->user()->can('server:edit')) {}
         * @example
         * @example User::has_scope('server:edit')
         */
        // Valid_scopes returns a list of scopes without a dynamic part
        // example: server:index, server:monitor
        // We need to define a gate for each scope
        collect(User::valid_scopes())->each(fn ($scope) => Gate::define($scope, fn (User $user) => $user->has_scope($scope)));


        // View
        Gate::define('server:view', function (User $user, Server $server) {
            if ($user->has_scope('server:index')) {
                Log::info("User {$user->id} used server:index to view");
                return true;
            }
            $dynamicGate = "server:{$server->uuid}:view";
            if ($user->has_scope($dynamicGate)) {
                Log::info("User {$user->id} used {$dynamicGate}");
                return true;
            }

            return false;
        });

        // Edit
        Gate::define('server:edit', function (User $user, Server $server) {
            if ($user->has_scope('server:index')) {
                Log::info("User {$user->id} used server:index for editing");
                return true;
            }
            $dynamicGate = "server:{$server->uuid}:edit";
            if ($user->has_scope($dynamicGate)) {
                Log::info("User {$user->id} used {$dynamicGate}");
                return true;
            }

            return false;
        });

        // Delete
        Gate::define('server:delete', function (User $user, Server $server) {
            if ($user->has_scope('server:index')) {
                Log::info("User {$user->id} used server:index for deleting");

                return true;
            }
            $dynamicGate = "server:{$server->uuid}:delete";
            if ($user->has_scope($dynamicGate)) {
                Log::info("User {$user->id} used {$dynamicGate}");

                return true;
            }

            return false;
        });
    }
}
