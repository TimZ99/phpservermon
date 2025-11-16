<x-guest-layout>
    <div class="container min-vh-100 py-5 d-flex align-items-center justify-content-center">
        <div class="card w-100" style="max-width: 520px;">
            <div class="card-header">
                Confirm password?
            </div>
            <div class="card-body">
                <p class="card-text mb-4">
                    This is a secure area of the application. Please confirm your password before continuing.
                </p>

                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf

                    <!-- Email Address -->
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input id="password" class="form-control mb-2" type="password" name="password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />

                    <div class="d-flex justify-content-end mt-4">
                        <x-primary-button>
                            {{ __('Confirm') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
