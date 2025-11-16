<x-app-layout>
    <x-slot name="header">
        {{ __('Users') }}
    </x-slot>

    <div class="row g-4">
        @foreach($users as $user)
            @include('user.components.user-card', ['user' => $user])
        @endforeach
    </div>
</x-app-layout>
