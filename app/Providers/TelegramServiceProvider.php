<?php

namespace App\Providers;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\Telegram;
use NotificationChannels\Telegram\TelegramChannel;

/**
 * Class TelegramServiceProvider.
 */
class TelegramServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->bind(Telegram::class, static fn () => new Telegram(
            config('services.telegram-bot-api.token'),
            app(HttpClient::class),
            'https://api.telegram.org'
        ));

        Notification::resolved(static function (ChannelManager $service) {
            $service->extend('telegram', static fn ($app) => $app->make(TelegramChannel::class));
        });
    }
}
