<section class="card shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
            <div>
                <p class="text-uppercase text-muted small mb-1">{{ __('Basics') }}</p>
                <h2 class="h4 mb-1">{{ __('Profile Information') }}</h2>
                <p class="text-muted mb-0">{{ __("Update your account's profile information and email address.") }}</p>
            </div>
        </div>

        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>

        <form method="post" action="{{ route('profile.update') }}" class="row g-3">
            @csrf
            @method('patch')

            <div class="col-12">
                <label for="name" class="form-label">{{ __('Name') }}</label>
                <input id="name" name="name" class="form-control" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div class="col-12">
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input id="email" name="email" class="form-control" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="alert alert-warning d-flex justify-content-between align-items-center gap-3 mt-3 mb-0">
                        <div>
                            <p class="mb-1">{{ __('Your email address is unverified.') }}</p>
                            <p class="mb-0 small">{{ __('Click the link below and we will re-send the verification email.') }}</p>
                        </div>
                        <button class="btn btn-sm btn-warning" form="send-verification">
                            {{ __('Resend') }}
                        </button>
                    </div>

                    @if (session('status') === 'verification-link-sent')
                        <p class="text-success small mt-2 mb-0">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                @endif
            </div>

            <div class="col-12 col-md-6">
                <label for="phone" class="form-label">{{ __('Phone') }}</label>
                <input id="phone" name="phone" class="form-control" type="tel" value="{{ old('phone', $user->phone) }}" autocomplete="tel" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>

            <div class="col-12 col-md-6">
                <label for="telegram_user_id" class="form-label">{{ __('Telegram User ID') }}</label>
                <input id="telegram_user_id" name="telegram_user_id" class="form-control" type="number" value="{{ old('telegram_user_id', $user->telegram_user_id) }}" />
                <x-input-error class="mt-2" :messages="$errors->get('telegram_user_id')" />
                @if (! $telegramGloballyEnabled || ! $telegramBotConfigured)
                    <p class="text-warning small mt-2 mb-0">{{ __('Telegram notifications are disabled globally or the bot token is not configured.') }}</p>
                @endif
            </div>

            <div class="col-12 d-flex flex-wrap gap-3 align-items-center">
                <x-primary-button>{{ __('Save changes') }}</x-primary-button>
                <a href="{{ route('profile.test.telegram') }}" class="text-decoration-none">
                    <x-secondary-button type="button">{{ __('Test Telegram') }}</x-secondary-button>
                </a>
                <p class="text-muted small mb-0">{{ __('Saving updates re-syncs all notification settings immediately.') }}</p>
            </div>
        </form>
    </div>
</section>
