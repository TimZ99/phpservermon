<x-app-layout>
    @if(session('check_dispatched'))
    <div class="alert alert-info" role="alert">
        {{ __('Checks have been queued. This view will update as results arrive.') }}
    </div>
    @endif

    <div class="row d-flex">
    @can('checkAny', App\Models\Server::class)
        <a href="{{route('server.runBatch')}}">
            <button class="btn btn-secondary mb-4">
                {{ __('Run tests') }}
            </button>
        </a>
        @endcan
        @forelse ($servers as $server)
            <div class="col-sm-4 col-md-3 col-xl-3">
                <div class="card text-bg-{{ $server->statusCss }} mb-4" @can('view', $server) onclick="window.location.href='{{ route('server.show', $server->id) }}'" @endcan>
                    <div class="card-header">
                        <a href="{{ route('server.show', $server->id) }}">{{ $server->name }}</a>
                    </div>
                    <div class="card-body">
                        <div class="card-text my-0">
                            {{ __('Last check') }}:
                            @php
                                $overallStatus = strtolower($server->overall_status ?? 'unknown');
                                $badgeClass = match($overallStatus) {
                                    'success' => 'bg-success',
                                    'warning' => 'bg-warning text-dark',
                                    'fail', 'danger', 'error' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} my-1">{{ strtoupper($overallStatus) }}</span><br>
                            {{ $server->last_checked_at->timezone(config('app.timezone'))->format('M j, Y H:i:s') ?? __('Never') }}<br>
                            <div style="width:100%;">
                                @foreach ($server->show_status as $check)
                                    <div style="width: 10px; height: 10px; background-color: {{ $check['color'] }}; display: inline-block;" title="{{ $check['name'] }}"></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center">{{ __('No servers defined.') }}</p>
            <div class="w-100"></div>
            @can('manageAny', App\Models\Server::class)
                <button class="btn btn-primary" onclick="window.location.href='{{ route('server.create') }}'">{{ __('Add server') }}</button>
            @endcan
        @endforelse
    </div>
</x-app-layout>
