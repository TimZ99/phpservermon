<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdateRequest;
use App\Notifications\Messages\DynamicNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(UserUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function test_telegram()
    {
        try {
            Notification::send(Auth::user(), new DynamicNotification('test_message', ['text' => 'Test message for Telegram notification']));
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $message = 'Failed to send Telegram notification: '.$e->getMessage();
            logger()->error($message);

            return $message;
        }

        return 'Notification sent to user';
    }
}
