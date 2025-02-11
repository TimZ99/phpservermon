<x-app-layout>
    <x-slot name="header">
        Overview
    </x-slot>
    <ul class="list-group">
        @foreach($users as $user)
            @include('user.components.user-card', ['user' => $user])
        @endforeach
    </ul>
</x-app-layout>
