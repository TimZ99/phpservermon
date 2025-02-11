@props(['user'])
<li class="list-group-item">
    <a href="{{ route('user.show', $user->id) }}">{{$user->name}}</a>
</li>