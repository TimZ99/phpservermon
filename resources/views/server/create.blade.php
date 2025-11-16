<x-app-layout>
    <x-slot name="header">
        {{ __('Create server') }}
    </x-slot>

    @include('server.partials.update-server-information-form')
</x-app-layout>
