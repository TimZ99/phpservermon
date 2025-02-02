@props(['server'])
<div class="p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
    <a href="{{ route('server.show', $server->server_id) }}">{{$server->server_id}}</a>
</div>