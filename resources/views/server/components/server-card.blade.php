@props(['server'])
<li class="list-group-item">
    <a href="{{ route('server.show', $server->id) }}">{{$server->name}}</a>
</li>