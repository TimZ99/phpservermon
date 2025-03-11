<?php

namespace App\Providers;

use App\Gates\Gates;
use App\Settings\EmailSettings;
use App\Settings\GeneralSettings;
use App\Settings\NotificationSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

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
        // If the environment is local, prevent models from silently discarding
        // attributes that are not present in the database.
        Model::preventSilentlyDiscardingAttributes($this->app->environment('local'));

        // Register the application's gate definitions.
        Gates::boot();

        // set config
        Config::set('app.locale', app(GeneralSettings::class)->default_locale ?? Config::get('app.locale'));
        Config::set('app.timezone', app(GeneralSettings::class)->timezone ?? Config::get('app.timezone'));
        Config::set('email.from.name', app(EmailSettings::class)->from_name ?? Config::get('email.from.name'));
        Config::set('email.from.address', app(EmailSettings::class)->from_address ?? Config::get('email.from.address'));
        Config::set('notification.telegram_bot_token', app(NotificationSettings::class)->telegram_bot_token ?? Config::get('notification.telegram_bot_token'));
    }
}
