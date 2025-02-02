<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Overview
        </h2>
    </x-slot>
    <div class="flex">
        @foreach($servers as $server)
        <div class="w-1/4">
            <x-server-card :server="$server" />
        </div>
        @endforeach
    </div>
</x-app-layout>
