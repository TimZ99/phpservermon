<?php
namespace App\Notifications;

use NotificationChannels\Telegram\TelegramMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Bus\Queueable;

class ServerUpdate extends Notification implements ShouldQueue
{
    use Queueable;
    
    public function __construct()
    {
        $this->onQueue('notifications');
    }

    public function via($notifiable)
    {
        return ["telegram"];
    }

    public function toTelegram($notifiable)
    {
        if (!$notifiable->telegram_user_id) {
            return;
        }
        return TelegramMessage::create()
            ->to($notifiable->telegram_user_id)
            ->content("Your server has been updated.")
            ->button('View Server', 'https://example.com/server');
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