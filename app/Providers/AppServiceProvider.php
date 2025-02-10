<?php

namespace App\Providers;

use App\Gates\Gates;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventSilentlyDiscardingAttributes($this->app->environment('local'));
        Gates::boot();
    }
}
