<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Overview
        </h2>
    </x-slot>
    <div class="grid grid-cols-2 gap-4">
        @foreach($servers as $server)
            @include('server.components.server-card', ['server' => $server])
        @endforeach
    </div>
</x-app-layout>
