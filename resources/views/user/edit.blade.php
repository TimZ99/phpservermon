<x-app-layout>
    <x-slot name="header">
        {{ __('Edit user') }}
    </x-slot>

    <div class="card">
        <div class="card-body">
            @include('user.partials.update-user-information-form')
            @include('user.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
