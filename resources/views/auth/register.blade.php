<x-guest-layout>
    <div class="container min-vh-100 py-5 d-flex align-items-center justify-content-center">
        <div class="card w-100" style="max-width: 520px;">
            <div class="card-body">
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <!-- Name -->
                    <label for="name" class="form-label">{{ __('Name') }}</label>
                    <input id="name" class="form-control mb-2" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />

                    <!-- Email Address -->
                    <label for="email" class="form-label">{{ __('Email') }}</label>
                    <input id="email" class="form-control mb-2" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />

                    <!-- Password -->
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input id="password" class="form-control mb-2"
                                    type="password"
                                    name="password"
                                    required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />

                    <!-- Confirm Password -->
                    <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                    <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />

                    <div class="d-flex justify-content-end align-items-center mt-4 gap-3">
                        <a class="text-decoration-underline" href="{{ route('login') }}">
                            {{ __('Already registered?') }}
                        </a>

                        <x-primary-button outline>
                            {{ __('Register') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
