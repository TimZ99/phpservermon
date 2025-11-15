<section class="mb-4">
    <header>
        <h2>{{ __('Profile Information') }}</h2>
        <p class="mt-1">{{ __("Update your account's profile information and email address.") }}</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6">
        @csrf
        @method('patch')

        <div>
            <label for="name">{{ __('Name') }}</label>
            <input id="name" name="name" class="form-control mb-2" type="text" value="{{old('name', $user->name)}}" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email">{{ __('Email') }}</label>
            <input id="email" name="email" class="form-control mb-2" type="email" value="{{old('email', $user->email)}}" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <label for="phone">{{ __('Phone') }}</label>
            <input id="phone" name="phone" class="form-control mb-2" type="tel" value="{{old('phone', $user->phone)}}" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div>
            <label for="telegram_user_id">{{ __('Telegram User ID') }}</label>
            <input id="telegram_user_id" name="telegram_user_id" class="form-control mb-2" type="number" value="{{ old('telegram_user_id', $user->telegram_user_id) }}" />
            <x-input-error class="mt-2" :messages="$errors->get('telegram_user_id')" />
            @if (! $telegramGloballyEnabled || ! $telegramBotConfigured)
                <p class="text-warning small mb-2">{{ __('Telegram notifications are disabled globally or the bot token is not configured.') }}</p>
            @endif
            <a href="{{ route('profile.test.telegram') }}">
                <x-secondary-button type="button" class="mb-2">{{ __('Test Telegram') }}</x-secondary-button>
            </a>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </div>
    </form>
</section>
