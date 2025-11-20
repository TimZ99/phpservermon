<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyRegisterOptionsAction;
use Spatie\LaravelPasskeys\Actions\StorePasskeyAction;
use Spatie\LaravelPasskeys\Models\Passkey;
use Spatie\LaravelPasskeys\Support\Config;
use Throwable;

class PasskeyController extends Controller
{
    public function options(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $generateAction = Config::getAction(
            'generate_passkey_register_options',
            GeneratePasskeyRegisterOptionsAction::class
        );

        $options = $generateAction->execute($request->user());

        session()->put('passkey-registration-options', $options);
        session()->put('passkey-registration-name', $validated['name']);

        return response()->json([
            'options' => $options,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'passkey' => ['required', 'json'],
        ]);

        $options = session()->pull('passkey-registration-options');

        if (! $options) {
            throw ValidationException::withMessages([
                'passkey' => __('passkeys::passkeys.error_something_went_wrong_generating_the_passkey'),
            ]);
        }

        $storeAction = Config::getAction('store_passkey', StorePasskeyAction::class);

        try {
            $storeAction->execute(
                $request->user(),
                $validated['passkey'],
                $options,
                $request->getHost(),
                ['name' => session()->pull('passkey-registration-name', 'Passkey')]
            );
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'passkey' => $e->getMessage(),
            ]);
        }

        session()->flash('status', 'passkey-added');

        return response()->json(['status' => 'created']);
    }

    public function destroy(Request $request, Passkey $passkey): RedirectResponse
    {
        if ((int) $passkey->authenticatable_id !== (int) $request->user()->getAuthIdentifier()) {
            abort(403);
        }

        $passkey->delete();

        return Redirect::route('profile.edit')->with('status', 'passkey-removed');
    }
}
