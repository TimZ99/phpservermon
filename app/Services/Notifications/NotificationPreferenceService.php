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
        /** @var NotificationPreference|null $preference */
        $preference = NotificationPreference::query()
            ->where('user_id', $user->id)
            ->where('channel', $channel)
            ->where(function (Builder $query) use ($server, $checkName) {
                $query->where(function (Builder $q) {
                    $q->whereNull('server_id')
                        ->whereNull('check_name');
                })->orWhere(function (Builder $q) use ($server) {
                    $q->where('server_id', $server->id)
                        ->whereNull('check_name');
                })->orWhere(function (Builder $q) use ($checkName) {
                    $q->whereNull('server_id')
                        ->where('check_name', $checkName);
                })->orWhere(function (Builder $q) use ($server, $checkName) {
                    $q->where('server_id', $server->id)
                        ->where('check_name', $checkName);
                });
            })
            ->orderByRaw('case when server_id is not null and check_name is not null then 1 when server_id is not null then 2 when check_name is not null then 3 else 4 end')
            ->first();

        return $preference;
    }
}
