<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Overview
        </h2>
    </x-slot>
    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
        <a href="{{route('server.update', $server->id)}}">
            <button class="btn btn-secondary mb-4">
                {{ __('Edit server') }}
            </button>
        </a>
        <br>
        Server overview<br><br>
        Server id {{ $server->id }}<br>
        Name {{ $server->name }}<br>
        IP {{ $server->ip }}<br>
        Port {{ $server->port }}<br>
    </div>
</x-app-layout>
