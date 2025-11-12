<?php

namespace App\Notifications\Channels\Telegram;

use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TelegramChannel
{
    /**
     * Build the message for Telegram from the Notification instance.
     */
    protected function buildMessage(object $notifiable, object $notification): string
    {
        if (method_exists($notification, 'toTelegram')) {
            return (string) $notification->toTelegram($notifiable);
        }

        if (property_exists($notification, 'data') && is_array($notification->data ?? null) && isset($notification->data['text'])) {
            return (string) $notification->data['text'];
        }

        return 'You have a new notification.';
    }

    /**
     * Send the notification via Telegram
     *
     * @param  object  $notifiable  The user or entity to notify.
     * @param  Notification  $notification  The notification instance.
     */
    public function send(object $notifiable, Notification $notification): void
    {

        $chatId = $notifiable->routeNotificationFor('telegram', $notification);
        if (! $chatId) {
            return;
        }

        $settings = app(\App\Settings\NotificationSettings::class);

        $message = $this->buildMessage($notifiable, $notification);

        TelegramMessage::create($message)
            ->token($settings->telegram_bot_token)
            ->to($chatId)
            ->send();
    }
}
