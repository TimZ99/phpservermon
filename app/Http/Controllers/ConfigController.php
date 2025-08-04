<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfigUpdateRequest;
use App\Settings\EmailSettings;
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
        $emailSettings = app(\App\Settings\EmailSettings::class);
        $notificationSettings = app(\App\Settings\NotificationSettings::class);

        // Some values also have a value in Config::get of the .ENV file.
        return view('config.edit', [
            'locale' => Config::get('app.locale'),
            'timezone' => Config::get('app.timezone'),
            'email_notifications_enabled' => $emailSettings->email_notifications_enabled,
            'from_name' => Config::get('email.from.name'),
            'from_address' => Config::get('email.from.address'),
            'telegram_notifications_enabled' => $notificationSettings->telegram_notifications_enabled,
            'telegram_bot_token' => Config::get('notification.telegram_bot_token'),
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
        EmailSettings $emailSettings,
        NotificationSettings $notificationSettings
    ) {
        $this->authorize('config:manage');
        $request->validated();

        // Update the config
        $generalSettings->default_locale = $request->locale;
        $generalSettings->timezone = $request->timezone;
        $generalSettings->save();

        $emailSettings->email_notifications_enabled = $request->boolean('email_notifications_enabled');
        $emailSettings->from_name = $request->from_name ?? null;
        $emailSettings->from_address = $request->from_address ?? null;
        $emailSettings->save();

        $notificationSettings->telegram_notifications_enabled = $request->boolean('telegram_notifications_enabled');
        $notificationSettings->telegram_bot_token = $request->telegram_bot_token ?? null;
        $notificationSettings->save();

        // Return the config edit page with a success message
        return to_route('config.edit')->with('success', 'Configuration updated successfully.');
    }
}
