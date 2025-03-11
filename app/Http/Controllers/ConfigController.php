<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfigUpdateRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Settings\GeneralSettings;
use App\Settings\EmailSettings;
use App\Settings\NotificationSettings;

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
     * Admin-only function
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        // Check if the user is an admin
        Gate::authorize('admin-only');

        // Return the config edit page
        return view('config.edit', [
            'locale' => Config::get('app.locale'),
            'timezone' => Config::get('app.timezone'),
            'from_name' => Config::get('email.from.name'),
            'from_address' => Config::get('email.from.address'),
            'telegram_bot_token' => Config::get('notification.telegram_bot_token'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Admin-only function
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(
        ConfigUpdateRequest $request,
        GeneralSettings $generalSettings,
        EmailSettings $emailSettings,
        NotificationSettings $notificationSettings)
    {
        // Check if the user is an admin
        Gate::authorize('admin-only');

        $request->validated();

        // Update the config
        $generalSettings->default_locale = $request->locale;
        $generalSettings->timezone = $request->timezone;
        $generalSettings->save();

        $emailSettings->from_name = $request->from_name;
        $emailSettings->from_address = $request->from_address;
        $emailSettings->save();

        $notificationSettings->telegram_bot_token = $request->telegram_bot_token;
        $notificationSettings->save();

        // Log the config update
        Log::info('Configuration updated', [
            'locale' => $request->locale,
            'timezone' => $request->timezone,
            'from_name' => $request->from_name,
            'from_address' => $request->from_address,
            'telegram_bot_token' => $request->telegram_bot_token,
        ]);

        // Return the config edit page with a success message
        return to_route('config.edit')->with('success', 'Configuration updated successfully.');
    }
}
