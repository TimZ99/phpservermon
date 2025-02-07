@props(['server'])
<li class="list-group-item">
    <a href="{{ route('server.show', $server->id) }}">{{$server->id}}</a>
</li>