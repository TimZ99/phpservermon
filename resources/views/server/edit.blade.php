<x-app-layout>
    <x-slot name="header">
        {{ __('Edit server') }}
    </x-slot>

    <div class="card">
        <div class="card-body">
            @include('server.partials.update-server-information-form')
            @include('server.partials.delete-server-form')
        </div>
    </div>
</x-app-layout>
