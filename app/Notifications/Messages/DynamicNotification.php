<?php

namespace App\Notifications\Messages;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DynamicNotification extends Notification
{
    // (Optionally implement ShouldQueue if you want to queue notifications)
    use Queueable;

    public string $notification_event;

    public array $data;  // data payload (like monitor info, etc.)

    public function __construct(string $notification_event, array $data = [], array $channels = [])
    {
        $this->notification_event = $notification_event;
        $this->data = $data;
    }

    /**
     * Determine which channels to send the notification through.
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        // 1. Load global settings for channels
        $settings = app(\App\Settings\NotificationSettings::class);

        if ($this->event = 'test_message') {
            $user_preferences = [new \App\Models\NotificationPreference([
                'user_id' => $notifiable->id,
                'channel' => 'telegram',
                'event' => 'test_message',
                'enabled' => true,
            ])];
        } else {
            $user_preferences = \App\Models\NotificationPreference::query()
                ->where('user_id', $notifiable->id)
                ->where('event', $this->event)
                ->get()
                ->keyBy('channel');
        }
        // 3. Determine for each channel if we should notify
        if (
            $settings->email_global_enabled
            && ! empty($notifiable->email)
        ) {
            $channels[] = 'mail';  // use Laravel's mail channel
        }
        if (
            $settings->telegram_global_enabled
            && ! empty($notifiable->telegram_user_id)
        ) {
            $channels[] = \App\Notifications\Channels\TelegramChannel::class;
        }

        return $channels;
    }

    // Channel-specific message builders:

    public function toMail(object $notifiable): MailMessage
    {
        // Use localization keys for subject/content:
        $subject = __('notifications.'.$this->notification_event.'.subject', $this->data);
        $line1 = __('notifications.'.$this->notification_event.'.message', $this->data);

        return (new MailMessage)
            ->subject($subject)
            ->line($line1);
    }

    public function toSms(object $notifiable): string
    {
        // Return plain text for SMS (could also return a custom SmsMessage object)
        return __(
            'notifications.'.$this->notification_event.'.sms_text',
            $this->data
        );
    }
}
