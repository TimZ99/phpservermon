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
            <a href="{{route('server.runChecks', $server->id)}}">
                <button class="btn btn-secondary mb-4">
                    {{ __('Run tests') }}
                </button>
            </a>
            <br>
            <b>Server id</b> {{ $server->id }}<br>
            <b>Name</b> {{ $server->name }}<br>
            <b>IP</b> {{ $server->ip }}<br>
            <b>Port</b> {{ $server->port }}<br>
            @isset($server->users) <b>Users</b> @foreach($server->users as $user) {{ $user->name }}, @endforeach <br>@endisset
            <b>Ran checks</b><br> @foreach($server->check_histories as $check) {{ $check->Name }} - {{ $check->message }}<br> @endforeach<br>
            <b>Created at</b> {{ $server->created_at }}<br>
            <b>Updated at</b> {{ $server->updated_at }}<br>
            <b>Check settings</b>{{ $server->check_settings }}<br>
        </div>
    </div>
</x-app-layout>
