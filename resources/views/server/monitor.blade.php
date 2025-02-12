<x-app-layout>
    <div class="row d-flex">
        @forelse ($servers as $server)
            <div class="col-sm-4 col-md-3 col-xl-2">
                <div class="card text-bg-{{ $server->statusCss }} mb-4" onclick="window.location.href='{{ route('server.show', $server->id) }}'">
                    <div class="card-header">
                        <a href="{{ route('server.show', $server->id) }}">{{ $server->name }}</a>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            {{ __('Last online') }}: {{ $server->last_online_nice }}<br>
                            {{ __('Last check') }}: {{ $server->last_checked_nice }}
                            @if ($server->status === 'online')
                                <br>
                                {{ __('Last offline') }}: {{ $server->last_offline_nice }} {{ $server->last_offline_duration_nice }}<br>
                                {{ __('Response time') }}: {{ (int) round($server->rtime * 1000) }} ms
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center">{{ __('No servers defined.') }}</p>
            <div class="w-100"></div>
            @can('admin-only')
                <button class="btn btn-primary" onclick="window.location.href='{{ route('server.create') }}'">{{ __('Add server') }}</button>
            @endcan
        @endforelse
    </div>
</x-app-layout>

