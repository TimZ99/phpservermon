<?php

namespace App\Notifications;

use App\Models\CheckHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Telegram\TelegramMessage;

class ServerUpdate extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected string $run_curl_batch_id, protected string $server_checks_batch_id)
    {
        $this->run_curl_batch_id = $run_curl_batch_id;
        $this->server_checks_batch_id = $server_checks_batch_id;
        $this->onQueue('notifications');
    }

    public function via($notifiable)
    {
        if ($notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable) {
            return array_keys($notifiable->routes);
        }
        $routes = [];
        if (isset($notifiable->telegram_user_id)) {
            $routes[] = 'telegram';
        }

        return $routes;
    }

    public function toTelegram($notifiable)
    {
        $telegram_user_id = $notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable
        ? $notifiable->routeNotificationFor('telegram')
        : $notifiable->telegram_user_id;

        Log::info('Sending Telegram notification.', [$notifiable]);

        $checks = CheckHistory::where('run_curl_batch_id', $this->run_curl_batch_id)->where('server_checks_batch_id', $this->server_checks_batch_id)->get();
        // $checks = CheckHistory::all();
        Log::critical('Checks:', [
            'run_curl_batch_id' => $this->run_curl_batch_id,
            'server_checks_batch_id' => $this->server_checks_batch_id,
            'checks' => $checks,
        ]);

        return TelegramMessage::create('Your server has been updated successfully!')
            ->to($telegram_user_id)
            ->line('run\_curl\_batch\_id: ['.$this->run_curl_batch_id.'](http://localhost/telescope/batches/'.$this->run_curl_batch_id.')')
            ->line('server\_checks\_batch\_id: ['.$this->server_checks_batch_id.'](http://localhost/telescope/batches/'.$this->server_checks_batch_id.')')
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
