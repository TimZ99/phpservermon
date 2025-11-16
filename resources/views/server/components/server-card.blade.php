@props(['server'])
<div class="col-12 col-md-6 col-xl-4">
    <a href="{{ route('server.show', $server->id) }}" class="text-decoration-none text-reset">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-uppercase text-muted small mb-1">{{ __('Server') }}</p>
                        <h2 class="h5 mb-1">{{ $server->name }}</h2>
                        <p class="text-body-secondary mb-0">{{ $server->ip }}</p>
                    </div>
                    <span class="badge text-bg-{{ $server->statusCss }}">{{ strtoupper($server->statusCss) }}</span>
                </div>
            </div>
        </div>
    </a>
</div>
