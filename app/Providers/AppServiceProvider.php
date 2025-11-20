<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the telescope service provider if the environment is local
        // and the telescope package is installed.
        if (
            $this->app->environment('local') &&
            class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)
        ) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // If the environment is local, prevent models from silently discarding
        // attributes that are not present in the database.
        Model::preventSilentlyDiscardingAttributes($this->app->environment('local'));
        \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
            $scope->setTag('page.locale', $this->app->getLocale());
        });
    }
}
