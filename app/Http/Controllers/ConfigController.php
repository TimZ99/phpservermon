<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfigUpdateRequest;
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
        $notificationSettings = app(\App\Settings\NotificationSettings::class);

        // Some values also have a value in Config::get of the .ENV file.
        return view('config.edit', [
            'locale' => Config::get('app.locale'),
            'timezone' => Config::get('app.timezone'),
            'email_global_enabled' => $notificationSettings->email_global_enabled,
            'email_from_name' => Config::get('email.from.name'),
            'email_from_address' => Config::get('email.from.address'),
            'telegram_global_enabled' => $notificationSettings->telegram_global_enabled,
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
        NotificationSettings $notificationSettings
    ) {
        $this->authorize('config:manage');
        $request->validated();

        // Update the config
        $generalSettings->default_locale = $request->locale;
        $generalSettings->timezone = $request->timezone;
        $generalSettings->save();

        $notificationSettings->email_global_enabled = $request->boolean('email_global_enabled');
        $notificationSettings->email_from_name = $request->email_from_name ?? null;
        $notificationSettings->email_from_address = $request->email_from_address ?? null;
        $notificationSettings->telegram_global_enabled = $request->boolean('telegram_global_enabled');
        $notificationSettings->telegram_bot_token = $request->telegram_bot_token ?? null;
        $notificationSettings->save();

        // Return the config edit page with a success message
        return to_route('config.edit')->with('success', 'Configuration updated successfully.');
    }
}
