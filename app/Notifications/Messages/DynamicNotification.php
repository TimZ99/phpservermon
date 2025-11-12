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

    public array $channels;  // channels to send this notification through

    public function __construct(string $notification_event, array $data = [], array $channels = [])
    {
        $this->notification_event = $notification_event;
        $this->data = $data;
        $this->channels = $channels;
    }

    /**
     * Determine which channels to send the notification through.
     */
    public function via(object $notifiable): array
    {
        return $this->getChannels($notifiable);
    }

    protected function getChannels(object $notifiable): array
    {
        // If channels are specified, use them
        if (! empty($this->channels)) {
            return array_unique($this->channels);
        }
        $channels = [];

        $settings = app(\App\Settings\NotificationSettings::class);

        // Channel resolution is based on global settings and available user contact info
        if ($settings->email_global_enabled && ! empty($notifiable->email)) {
            $channels[] = 'mail'; // Laravel's built-in mail channel
        }

        if ($settings->telegram_global_enabled && ! empty($notifiable->telegram_user_id)) {
            $channels[] = \App\Notifications\Channels\Telegram\TelegramChannel::class;
        }

        return array_unique($channels);
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
