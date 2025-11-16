<x-guest-layout>
    <div class="container min-vh-100 py-5 d-flex align-items-center justify-content-center">
        <div class="card w-100" style="max-width: 520px;">
            <div class="card-header">
                Forgot your password?
            </div>
            <div class="card-body">
                <p class="card-text mb-4">
                    No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
                </p>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <!-- Email Address -->
                    <label for="email" class="form-label">{{ __('Email') }}</label>
                    <input id="email" class="form-control mb-2" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />

                    <div class="d-flex justify-content-end mt-4">
                        <x-primary-button>
                            {{ __('Email Password Reset Link') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
