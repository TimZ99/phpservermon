<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
            Overview
        </h2>
    </x-slot>
    <ul class="list-group">
        @foreach($servers as $server)
            @include('server.components.server-card', ['server' => $server])
        @endforeach
    </ul>
</x-app-layout>
