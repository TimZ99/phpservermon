<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <div class="mt-4 col-12 col-md-9 col-lg-7 col-xl-6 col-xxl-5 container d-flex align-items-center justify-content-center">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Address -->
                    <label for="email" class="sr-only">{{ __('Email') }}</label>
                    <input id="email" class="form-control mb-2" type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />

                    <!-- Password -->
                    <label for="password" class="sr-only">{{ __('Password') }}</label>
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
                    <div class="flex items-center justify-end mt-4">
                        @if (Route::has('password.request'))
                            <a class="underline text-sm" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                        <x-primary-button class="ms-4" outline>
                            {{ __('Login') }}
                        </x-primary-button>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>