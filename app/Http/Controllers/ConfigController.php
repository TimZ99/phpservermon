<?php

namespace App\Http\Controllers;

use App\Enums\QueueName;
use App\Http\Requests\ConfigUpdateRequest;
use App\Services\Queue\QueueHeartbeatService;
use App\Settings\GeneralSettings;
use App\Settings\NotificationSettings;
use Illuminate\Support\Facades\Config;

/**
 * Routing:
 *
 * @group Server
 *
 * @authenticated
 *
 * @middleware can:not-suspended
 */
class ConfigController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $this->authorize('config:manage');

        // Return the config edit page
        $generalSettings = app(GeneralSettings::class);
        $notificationSettings = app(\App\Settings\NotificationSettings::class);

        // Some values also have a value in Config::get of the .ENV file.
        return view('config.edit', [
            'locale' => Config::get('app.locale'),
            'timezone' => Config::get('app.timezone'),
            'check_history_retention_days' => $generalSettings->check_history_retention_days,
            'email_global_enabled' => $notificationSettings->email_global_enabled,
            'email_from_name' => Config::get('email.from.name'),
            'email_from_address' => Config::get('email.from.address'),
            'email_host' => Config::get('mail.mailers.smtp.host'),
            'email_port' => Config::get('mail.mailers.smtp.port'),
            'email_encryption' => Config::get('mail.mailers.smtp.scheme'),
            'email_username' => Config::get('mail.mailers.smtp.username'),
            'telegram_global_enabled' => $notificationSettings->telegram_global_enabled,
            'telegram_bot_token' => Config::get('notification.telegram_bot_token'),
            'queue_connection' => Config::get('queue.default'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(
        ConfigUpdateRequest $request,
        GeneralSettings $generalSettings,
        NotificationSettings $notificationSettings
    ) {
        $this->authorize('config:manage');
        $request->validated();

        // Update the config
        $generalSettings->default_locale = $request->locale;
        $generalSettings->timezone = $request->timezone;
        $generalSettings->check_history_retention_days = $request->integer('check_history_retention_days', 7);
        $generalSettings->save();

        $notificationSettings->email_global_enabled = $request->boolean('email_global_enabled');
        $notificationSettings->email_from_name = $request->filled('email_from_name') ? $request->email_from_name : null;
        $notificationSettings->email_from_address = $request->filled('email_from_address') ? $request->email_from_address : null;
        $notificationSettings->email_host = $request->filled('email_host') ? $request->email_host : null;
        $notificationSettings->email_port = $request->filled('email_port') ? (int) $request->email_port : null;
        $notificationSettings->email_encryption = $request->filled('email_encryption') ? $request->email_encryption : null;
        $notificationSettings->email_username = $request->filled('email_username') ? $request->email_username : null;
        $notificationSettings->email_password = $request->filled('email_password') ? $request->email_password : null;
        $notificationSettings->telegram_global_enabled = $request->boolean('telegram_global_enabled');
        $notificationSettings->telegram_bot_token = $request->telegram_bot_token ?? null;
        $notificationSettings->save();

        // Return the config edit page with a success message
        return to_route('config.edit')->with('success', 'Configuration updated successfully.');
    }

    public function heartbeat(QueueHeartbeatService $service)
    {
        $this->authorize('config:manage');

        $alive = $service->isAlive(QueueName::CURL);
        $last = $service->lastBeat(QueueName::CURL);

        return response()->json([
            'alive' => $alive,
            'last_beat' => $last?->toIso8601String(),
        ]);
    }
}
