<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TelegramChannel
{
    /**
     * Format the message for Telegram
     *
     * @param  Notification  $notification  The notification instance.
     */
    protected function createMessage(Notification $notification): string
    {
        return $notification->data['text'];
    }

    /**
     * Send the notification via Telegram
     *
     * @param  object  $notifiable  The user or entity to notify.
     * @param  Notification  $notification  The notification instance.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        $chatId = $notifiable->telegram_user_id ?? null;
        if (! $chatId) {
            return;
        }

        $settings = app(\App\Settings\NotificationSettings::class);

        TelegramMessage::create($this->createMessage($notification))
            ->token($settings->telegram_bot_token)
            ->to($chatId)
            ->send();
    }
}

class TelegramChannelUser extends \App\Models\User
{
    /**
     * Route notifications for the Telegram channel.
     */
    public function routeNotificationForTelegram(): int
    {
        return $this->telegram_user_id;
    }
}
