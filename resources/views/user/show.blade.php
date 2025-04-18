<x-app-layout>
    <x-slot name="header">
        User details
    </x-slot>
    <div class="card">
        <div class="card-header">
            User details
        </div>
        <div class="card-body">
            <a href="{{route('user.edit', $user->id)}}">
                <button class="btn btn-secondary mb-4">
                    {{ __('Edit user') }}
                </button>
            </a>
            <br>
            <b>User id</b> {{ $user->id }}<br>
            <b>Name</b> {{ $user->name }}<br>
            <b>Email</b> {{ $user->email }}<br>
            @isset($user->servers) <b>Scopes</b> @foreach($user->scopes  as $scope) {{ $scope }}, @endforeach <br>@endisset
            <b>Suspended</b> {{ $user->suspended }}<br>
            @isset($user->servers) <b>Servers</b> @foreach($user->servers  as $server) {{ $server->name }}, @endforeach <br>@endisset
            
            @foreach ($user->notifications as $notification) {{ $notification->type }} @endforeach
        </div>
    </div>
</x-app-layout>
