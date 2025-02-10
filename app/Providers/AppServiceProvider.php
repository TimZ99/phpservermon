<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\Response;

class AppServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
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
