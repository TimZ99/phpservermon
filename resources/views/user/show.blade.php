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
            @if($user->suspended === true)<b>Suspended</b> = true<br>@endif
            @isset($user->scopes) <b>Scopes</b> <ul> @foreach( $user->scopes as $scope)<li>{{ $scope }}</li> @endforeach </ul> @endisset
            @isset($user->servers) <b>Servers</b> <ul> @foreach($user->servers as $server)<li>{{ $server->name }}</li> @endforeach </ul> @endisset

            @foreach ($user->notifications as $notification) {{ $notification->type }} @endforeach
        </div>
    </div>
</x-app-layout>