@props(['user'])
@props(['user'])
<div class="col-12 col-md-6 col-xl-4">
    <a href="{{ route('user.show', $user->id) }}" class="text-decoration-none text-reset">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-uppercase text-muted small mb-1">{{ __('User') }}</p>
                <h2 class="h5 mb-1">{{ $user->name }}</h2>
                <p class="text-body-secondary mb-0">{{ $user->email }}</p>
            </div>
        </div>
    </a>
</div>
