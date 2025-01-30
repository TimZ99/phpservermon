<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Overview
        </h2>
    </x-slot>
    <div class="font-medium text-base text-gray-800 dark:text-gray-200">
        Serverlijst<br>
        @foreach ($servers as $server)
            This is Server {{ $server->server_id }}<br>
        @endforeach
    </div>
</x-app-layout>
