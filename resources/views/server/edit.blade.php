<x-app-layout>
    <x-slot name="header">
        {{ __('Edit server') }}
    </x-slot>

    <div class="vstack gap-4">
        @include('server.partials.update-server-information-form')
        @include('server.partials.delete-server-form')
    </div>
</x-app-layout>
