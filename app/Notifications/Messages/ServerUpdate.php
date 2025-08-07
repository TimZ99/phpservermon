<?php

namespace App\Notification\Messages;

use App\Models\CheckHistory;
use App\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Telegram\TelegramMessage;

class ServerUpdate extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected string $run_curl_batch_id, protected string $server_checks_batch_id, protected Server $server)
    {
        $this->run_curl_batch_id = $run_curl_batch_id;
        $this->server_checks_batch_id = $server_checks_batch_id;
        $this->server = $server;
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

        // Get previous run_curl_batch_id
        $previousRunCurlBatchId = CheckHistory::where('server_id', $this->server->id)
            ->where('run_curl_batch_id', '>', $this->run_curl_batch_id)
            ->orderBy('created_at', 'desc')
            ->distinct('run_curl_batch_id')
            ->take(1)->pluck('run_curl_batch_id');

        if (! isset($previousRunCurlBatchId[0])) {
            $previousRunCurlBatchId[0] = '';
            Log::debug('No previous run_curl_batch_id found, setting to empty string.');
        }

        $server_checks_old = CheckHistory::where('run_curl_batch_id', $previousRunCurlBatchId[0])
            ->where('server_id', $this->server->id)
            ->orderBy('created_at', 'desc')->get()->toArray();

        $server_checks_new = CheckHistory::where('run_curl_batch_id', $this->run_curl_batch_id)
            ->where('server_id', $this->server->id)
            ->orderBy('created_at', 'desc')->get()->toArray();

        Log::debug('Fetching server checks', [
            'previousRunCurlBatchId' => $previousRunCurlBatchId[0],
            'server_checks_old' => $server_checks_old,
            'server_checks_new' => $server_checks_new,
        ]);

        $content = '';
        foreach ($server_checks_new as $value) {
            $old_check = array_filter($server_checks_old, function ($check) use ($value) {
                return $check['name'] === $value['name'];
            });

            $value['status'] = str_replace(['success', 'warning', 'error'], ['🟢', '🟠', '🔴'], $value['status']);

            if (! empty($old_check)) {
                $old_check = array_shift($old_check);

                $old_check['status'] = str_replace(['success', 'warning', 'error'], ['🟢', '🟠', '🔴'], $old_check['status']);
            } else {
                $old_check['status'] = '⚪️';
            }

            $content .= $old_check['status'].'➡️'.$value['status'].' '.$value['name']."\n";
        }

        return TelegramMessage::create()
            ->to($telegram_user_id)
            ->line('*'.$this->server->name.'*')
            ->line($this->run_curl_batch_id)
            ->line($this->server_checks_batch_id)
            ->escapedLine($content)
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
