<x-app-layout>
    <x-slot name="header">
        Server details
    </x-slot>
    <div class="card">
        <div class="card-header">
            Server details
        </div>
        <div class="card-body">
            <a href="{{route('server.update', $server->id)}}">
                <button class="btn btn-secondary mb-4">
                    {{ __('Edit server') }}
                </button>
            </a>
            <br>
            <b>Server id</b> {{ $server->id }}<br>
            <b>Name</b> {{ $server->name }}<br>
            <b>IP</b> {{ $server->ip }}<br>
            <b>Port</b> {{ $server->port }}<br>
        </div>
    </div>
</x-app-layout>
