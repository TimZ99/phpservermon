<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Overview
        </h2>
    </x-slot>
    <div class="font-medium text-base text-gray-800 dark:text-gray-200">
        Server overview<br><br>
        Server id {{ $server->server_id }}<br>
        Name {{ $server->name }}<br>
        IP {{ $server->ip }}<br>
        Port {{ $server->port }}<br>
    </div>
</x-app-layout>
