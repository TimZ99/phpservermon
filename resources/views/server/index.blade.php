<x-app-layout>
    <x-slot name="header">
        Overview
    </x-slot>
    <ul class="list-group">
        @foreach($servers as $server)
            @include('server.components.server-card', ['server' => $server])
        @endforeach
    </ul>
</x-app-layout>
