<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Telegram\TelegramMessage;

class ServerUpdate extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue('notifications');
    }

    public function via($notifiable)
    {
        if($notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable) {
            return array_keys($notifiable->routes);
        }
        $routes = [];
        if(isset($notifiable->telegram_user_id)) {
            $routes[] = 'telegram';
        }
        
        return $routes;
    }

    public function toTelegram($notifiable)
    {
        $telegram_user_id = $notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable
        ? $notifiable->routeNotificationFor('telegram')
        : $notifiable->telegram_user_id;

        Log::info('Sending Telegram notification.', [$telegram_user_id]);

        return TelegramMessage::create('Your server has been updated successfully!')
            ->to($telegram_user_id)
            ->onError(function ($data) {
                Log::error('Failed to send Telegram notification', [
                    'chat_id' => $data['to'],
                    'error' => isset($data['exception']) ? $data['exception']->getMessage() : 'Unknown error',
                ]);
            });
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'content' => 'Your server has been updated.',
        ];
    }
}
