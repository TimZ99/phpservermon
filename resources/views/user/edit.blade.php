<x-app-layout>
    <x-slot name="header">
        {{ __('Edit user') }}
    </x-slot>

    <div class="d-flex flex-column gap-4">
        @include('user.partials.update-user-information-form')
        @include('user.partials.delete-user-form')
    </div>
</x-app-layout>
