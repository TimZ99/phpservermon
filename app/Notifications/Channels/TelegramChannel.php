<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\Telegram;
use NotificationChannels\Telegram\TelegramMessage;

class TelegramChannel
{
    // format the message for Telegram
    protected function toTelegram(Notification $notification): string
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

        TelegramMessage::create($this->toTelegram($notification))
            ->to($chatId)
            ->send(new Telegram($settings->telegram_bot_token));
    }
}
