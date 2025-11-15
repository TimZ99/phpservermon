<?php

namespace App\Notifications;

use App\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServerStatusChanged extends Notification
{
    use Queueable;

    /**
     * @param  array<string, array<int, array{name: string, message: string}>>  $summary
     * @param  array<int, string>  $channels
     */
    public function __construct(
        public Server $server,
        public string $status,
        public array $summary,
        public array $channels
    ) {}

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->server->name} status is {$this->statusLabel()}")
            ->line("{$this->server->name} is now {$this->statusLabel()}.")
            ->line($this->buildSummaryText());
    }

    public function toTelegram(object $notifiable): string
    {
        return sprintf(
            "*%s* is now *%s*\n%s",
            $this->server->name,
            $this->statusLabel(),
            $this->buildSummaryText()
        );
    }

    protected function statusLabel(): string
    {
        return strtoupper($this->status);
    }

    protected function buildSummaryText(): string
    {
        $failures = $this->formatEntries($this->summary['failures'] ?? [], 'FAIL');
        $warnings = $this->formatEntries($this->summary['warnings'] ?? [], 'WARN');
        $body = trim(implode("\n", array_filter([$failures, $warnings])));

        return $body === '' ? 'No detailed check results were recorded.' : $body;
    }

    /**
     * @param  array<int, array{name: string, message: string}>  $entries
     */
    protected function formatEntries(array $entries, string $label): string
    {
        if (empty($entries)) {
            return '';
        }

        $lines = array_map(fn ($entry) => "{$label}: {$entry['name']} – {$entry['message']}", $entries);

        return implode("\n", $lines);
    }
}
