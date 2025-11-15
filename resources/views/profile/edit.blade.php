<x-app-layout>
    <x-slot name="header">
        {{ __('Profile') }}
    </x-slot>
    <div class="row">
        @if(session('status') === 'telegram-test-sent')
            <div class="alert alert-success">{{ __('Telegram test message sent.') }}</div>
        @elseif(session('status') === 'telegram-disabled')
            <div class="alert alert-warning">{{ __('Telegram is disabled globally or bot token missing.') }}</div>
        @elseif(session('status') && str_contains(session('status'), 'Failed to send Telegram'))
            <div class="alert alert-danger">{{ session('status') }}</div>
        @endif
        @include('profile.partials.update-profile-information-form')
        <hr>
        @include('profile.partials.update-password-form')
        <hr>
        @include('profile.partials.delete-user-form')
    </div>
</x-app-layout>
