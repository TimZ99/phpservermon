<x-app-layout>
    <x-slot name="header">
        {{ __('Servers') }}
    </x-slot>

    <div class="row g-4">
        @foreach($servers as $server)
            @include('server.components.server-card', ['server' => $server])
        @endforeach
    </div>
</x-app-layout>
