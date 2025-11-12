<?php

namespace App\Services\Notifications;

use App\Models\NotificationPreference;
use App\Models\Server;
use App\Models\User;
use App\Notifications\Channels\Telegram\TelegramChannel;
use App\Settings\NotificationSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class NotificationPreferenceService
{
    public function __construct(private readonly NotificationSettings $settings) {}

    /**
     * @return array<int, string>
     */
    public function channelsFor(User $user, Server $server, string $checkName): array
    {
        $channels = $this->defaultChannels($user);

        return array_values(array_filter($channels, function (string $channel) use ($user, $server, $checkName) {
            return $this->isChannelEnabled($user, $server, $checkName, $channel);
        }));
    }

    /**
     * @return array<int, string>
     */
    protected function defaultChannels(User $user): array
    {
        $channels = [];

        if ($this->settings->email_global_enabled && ! empty($user->email)) {
            $channels[] = 'mail';
        }

        if ($this->settings->telegram_global_enabled && ! empty($user->telegram_user_id)) {
            $channels[] = TelegramChannel::class;
        }

        return $channels;
    }

    protected function isChannelEnabled(User $user, Server $server, string $checkName, string $channel): bool
    {
        $preference = $this->resolvePreference($user, $server, $checkName, $this->channelKey($channel));

        if (! $preference) {
            return true;
        }

        if ($preference->muted_until instanceof Carbon && $preference->muted_until->isFuture()) {
            return false;
        }

        return $preference->enabled;
    }

    protected function channelKey(string $channel): string
    {
        if ($channel === 'mail') {
            return 'mail';
        }

        if ($channel === TelegramChannel::class) {
            return 'telegram';
        }

        return strtolower(class_basename($channel));
    }

    protected function resolvePreference(User $user, Server $server, string $checkName, string $channel): ?NotificationPreference
    {
        return NotificationPreference::query()
            ->where('user_id', $user->id)
            ->where('channel', $channel)
            ->where(function (Builder $query) use ($server) {
                $query->whereNull('server_id')
                    ->orWhere('server_id', $server->id);
            })
            ->where(function (Builder $query) use ($checkName) {
                $query->whereNull('check_name')
                    ->orWhere('check_name', $checkName);
            })
            ->get()
            ->sortBy(fn (NotificationPreference $pref) => $this->preferencePriority($pref, $server, $checkName))
            ->first();
    }

    protected function preferencePriority(NotificationPreference $preference, Server $server, string $checkName): int
    {
        $serverSpecific = (string) $preference->server_id === (string) $server->id;
        $checkSpecific = $preference->check_name === $checkName;

        return match (true) {
            $serverSpecific && $checkSpecific => 0,
            $serverSpecific && ! $checkSpecific => 1,
            ! $serverSpecific && $checkSpecific => 2,
            default => 3,
        };
    }
}
