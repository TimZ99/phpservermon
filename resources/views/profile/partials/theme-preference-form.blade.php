@php
    $themePreference = old('theme_mode', $user->theme_mode ?? 'auto');
@endphp

<section class="card">
    <div class="card-body">
        <div class="mb-4">
            <p class="text-uppercase text-muted small mb-1">{{ __('Display') }}</p>
            <h2 class="h4 mb-1">{{ __('Theme preference') }}</h2>
            <p class="text-muted mb-0">{{ __('Choose how PHPServerMonitor looks across light, dark, or automatic modes.') }}</p>
        </div>

        <form method="post" action="{{ route('profile.update') }}" class="vstack gap-3">
            @csrf
            @method('patch')

            <div class="btn-group" role="group" aria-label="{{ __('Theme preference') }}">
                <input type="radio" class="btn-check" name="theme_mode" id="theme_mode_day" value="day" @checked($themePreference === 'day')>
                <label class="btn btn-outline-secondary" for="theme_mode_day">{{ __('Day') }}</label>

                <input type="radio" class="btn-check" name="theme_mode" id="theme_mode_night" value="night" @checked($themePreference === 'night')>
                <label class="btn btn-outline-secondary" for="theme_mode_night">{{ __('Night') }}</label>

                <input type="radio" class="btn-check" name="theme_mode" id="theme_mode_auto" value="auto" @checked($themePreference === 'auto')>
                <label class="btn btn-outline-secondary" for="theme_mode_auto">{{ __('Automatic') }}</label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('theme_mode')" />
            <p class="text-muted small mb-0">{{ __('Automatic follows your system preference, while Day or Night lock the interface to that mode and sync across devices once saved.') }}</p>

            <div>
                <x-primary-button>{{ __('Save theme') }}</x-primary-button>
            </div>
        </form>
    </div>
</section>
