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
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        if (is_null($notifiable->telegram_user_id)) {
            Log::error('No Telegram ID found for user.', [$notifiable]);

            return null;
        }
        Log::info('Sending Telegram notification.', [$notifiable->telegram_user_id]);

        return TelegramMessage::create('Your server has been updated successfully!')
            ->to($notifiable->telegram_user_id)
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
