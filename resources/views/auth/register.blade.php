<x-guest-layout>
    <div class="mt-4 col-12 col-md-9 col-lg-7 col-xl-6 col-xxl-5 container d-flex align-items-center justify-content-center">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <!-- Name -->
                    <label for="name" class="sr-only">{{ __('Name') }}</label>
                    <input id="name" class="form-control mb-2" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />

                    <!-- Email Address -->
                    <label for="email" class="sr-only">{{ __('Email') }}</label>
                    <input id="email" class="form-control mb-2" type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />

                    <!-- Password -->
                    <label for="password" class="sr-only">{{ __('Password') }}</label>
                    <input id="password" class="form-control mb-2"
                                    type="password"
                                    name="password"
                                    required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />

                    <!-- Confirm Password -->
                    <label for="password_confirmation" class="sr-only">{{ __('Confirm Password') }}</label>
                    <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />

                    <div class="flex items-center justify-end mt-4">
                        <a class="underline text-sm" href="{{ route('login') }}">
                            {{ __('Already registered?') }}
                        </a>

                        <x-primary-button class="ms-4" outline>
                            {{ __('Register') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
