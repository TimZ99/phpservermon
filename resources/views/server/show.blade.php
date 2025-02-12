<x-app-layout>
    <x-slot name="header">
        Server details
    </x-slot>
    <div class="card">
        <div class="card-header">
            Server details
        </div>
        <div class="card-body">
            <a href="{{route('server.edit', $server->id)}}">
                <button class="btn btn-secondary mb-4">
                    {{ __('Edit server') }}
                </button>
            </a>
            <br>
            <b>Server id</b> {{ $server->id }}<br>
            <b>Name</b> {{ $server->name }}<br>
            <b>IP</b> {{ $server->ip }}<br>
            <b>Port</b> {{ $server->port }}<br>
            @isset($server->users) <b>Users</b> @foreach($server->users as $user) {{ $user->name }}, @endforeach <br>@endisset
        </div>
    </div>
</x-app-layout>
