<x-app-layout>
    <x-slot name="header">
        {{ __('Profile') }}
    </x-slot>
    <div class="row">
        @include('profile.partials.update-profile-information-form')
        <hr>
        @include('profile.partials.update-password-form')
        <hr>
        @include('profile.partials.delete-user-form')
    </div>
</x-app-layout>
