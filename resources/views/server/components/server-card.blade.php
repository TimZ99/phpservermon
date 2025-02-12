@props(['server'])
<li class="list-group-item">
    <svg class="bd-placeholder-img rounded me-2" width="20" height="20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" preserveAspectRatio="xMidYMid slice" focusable="false"><rect width="100%" height="100%" fill="{{ $server->statusCssColor }}"></rect></svg>
    <a href="{{ route('server.show', $server->id) }}">{{$server->name}}</a>
</li>