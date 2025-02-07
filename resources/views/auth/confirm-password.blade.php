<x-guest-layout>
    <div class="mt-4 col-12 col-md-9 col-lg-7 col-xl-6 col-xxl-5 container d-flex align-items-center justify-content-center">
        <div class="card">
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
                    <label for="password">{{ __('Password') }}</label>
                    <input id="password" class="form-control mb-2" type="password" name="password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button>
                            {{ __('Confirm') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
