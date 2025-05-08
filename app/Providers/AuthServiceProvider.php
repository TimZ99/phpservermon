<?php

namespace App\Providers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
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

        Gate::before(function ($user, string $ability, array $arguments = []) {
            // Check if this is a policy call (i.e., has a model or alias)
            $target = $arguments[0] ?? null;
            $policy = $target ? Gate::getPolicyFor($target) : null;

            if ($policy) {
                if (! method_exists($policy, $ability)) {
                    Log::warning("Missing policy method '{$ability}'", [
                        'user_id' => $user->id,
                        'policy' => get_class($policy),
                        'target' => is_object($target) ? get_class($target) : $target,
                    ]);
                    throw new AuthorizationException("Missing policy method: {$ability}");
                }
            } else {
                // No policy — treat as a Gate
                if (! Gate::has($ability)) {
                    Log::warning("Gate '{$ability}' was called but not defined", [
                        'user_id' => $user->id,
                        'route' => request()->fullUrl(),
                    ]);
                    throw new AuthorizationException("Undefined gate: {$ability}");
                }
            }

            return null; // allow normal policy/gate evaluation to proceed
        });

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
