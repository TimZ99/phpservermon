<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <div class="container min-vh-100 py-5 d-flex align-items-center justify-content-center">
        <div class="card w-100" style="max-width: 520px;">
            <div class="card-body">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Address -->
                    <label for="email" class="form-label">{{ __('Email') }}</label>
                    <input id="email" class="form-control mb-2" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />

                    <!-- Password -->
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input id="password" class="form-control mb-2"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password" />

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />

                    <!-- Remember Me -->
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="remember_me" name="remember">
                        <label class="form-check-label" for="remember_me">
                            {{ __('Remember me') }}
                        </label>
                    </div>

                    <!-- Forgot password and login buttons -->
                    <div class="d-flex justify-content-end align-items-center mt-4 gap-3">
                        @if (Route::has('password.request'))
                            <a class="text-decoration-underline" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                        <x-primary-button outline>
                            {{ __('Login') }}
                        </x-primary-button>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
