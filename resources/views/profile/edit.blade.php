<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap align-items-baseline gap-3 text-white">
            <h1 class="h5 mb-0">{{ __('Profile & Security') }}</h1>
            <p class="mb-0 small opacity-75">{{ __('Manage the details that keep your account personal and secure.') }}</p>
        </div>
    </x-slot>

    <div class="row g-4">
        @if(session('status') === 'telegram-test-sent')
            <div class="col-12">
                <div class="alert alert-success text-bg-success mb-0">{{ __('Telegram test message sent.') }}</div>
            </div>
        @elseif(session('status') === 'telegram-disabled')
            <div class="col-12">
                <div class="alert alert-warning text-bg-warning mb-0">{{ __('Telegram is disabled globally or bot token missing.') }}</div>
            </div>
        @elseif(session('status') && str_contains(session('status'), 'Failed to send Telegram'))
            <div class="col-12">
                <div class="alert alert-danger text-bg-danger mb-0">{{ session('status') }}</div>
            </div>
        @elseif(session('status') === 'profile-updated')
            <div class="col-12">
                <div class="alert alert-success text-bg-success mb-0">{{ __('Your profile details have been updated.') }}</div>
            </div>
        @endif

        <div class="col-12 col-lg-8 d-flex flex-column gap-4">
            @include('profile.partials.update-profile-information-form')
            @include('profile.partials.theme-preference-form')
            @include('profile.partials.passkey-authentication-card')
            @include('profile.partials.update-password-form')
            @include('profile.partials.delete-user-form')
        </div>

        <div class="col-12 col-lg-4 d-flex flex-column gap-4">
            @include('profile.partials.profile-overview')
        </div>
    </div>
</x-app-layout>
