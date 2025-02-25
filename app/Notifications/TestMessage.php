<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Telegram\TelegramMessage;

class TestMessage extends Notification implements ShouldQueue
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
        if(isset($notifiable->email)) {
            $routes[] = 'mail';
        }
        
        return $routes;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $notifiable->email = $notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable
        ? $notifiable->routeNotificationFor('mail')
        : $notifiable->email;

        Log::info('Sending email notification.', [$notifiable->email]);

        return (new MailMessage)
            ->greeting('Hello!')
            ->line('One of your invoices has been paid!')
            ->line('Thank you for using our application!');
    }

    public function toTelegram(object $notifiable)
    {
        $telegram_user_id = $notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable
        ? $notifiable->routeNotificationFor('telegram')
        : $notifiable->telegram_user_id;

        Log::info('Sending Telegram notification.', [$telegram_user_id]);

        return TelegramMessage::create('Test message!')
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
            'content' => 'Test message.',
        ];
    }
}
