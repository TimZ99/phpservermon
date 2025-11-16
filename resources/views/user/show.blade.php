<x-app-layout>
    <x-slot name="header">
        User details
    </x-slot>
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <p class="text-uppercase text-muted small mb-1">{{ __('Overview') }}</p>
                    <h2 class="h4 mb-0">{{ $user->name }}</h2>
                    <p class="text-body-secondary mb-0">{{ $user->email }}</p>
                </div>
                @can('manage', $user)
                <div class="d-flex gap-2">
                    <a href="{{ route('user.edit', $user->id) }}" class="btn btn-outline-primary btn-sm">{{ __('Edit user') }}</a>
                </div>
                @endcan
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">{{ __('User ID') }}</dt>
                        <dd class="col-sm-7">{{ $user->id }}</dd>

                        <dt class="col-sm-5">{{ __('Phone') }}</dt>
                        <dd class="col-sm-7">{{ $user->phone ?: __('Not provided') }}</dd>

                        <dt class="col-sm-5">{{ __('Telegram user id') }}</dt>
                        <dd class="col-sm-7">{{ $user->telegram_user_id ?: __('Not linked') }}</dd>

                        <dt class="col-sm-5">{{ __('Suspended') }}</dt>
                        <dd class="col-sm-7">
                            <span class="badge {{ $user->suspended ? 'text-bg-danger' : 'text-bg-success' }}">
                                {{ $user->suspended ? __('Yes') : __('No') }}
                            </span>
                        </dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <div class="p-3 border rounded h-100">
                        <p class="text-uppercase text-muted small mb-2">{{ __('Scopes') }}</p>
                        @if(! empty($user->scopes))
                            <ul class="list-unstyled mb-0">
                                @foreach($user->scopes as $scope)
                                    <li class="small">{{ $scope }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-body-secondary mb-0">{{ __('No scopes assigned') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <p class="text-uppercase text-muted small mb-2">{{ __('Servers') }}</p>
                @if($user->servers && $user->servers->isNotEmpty())
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-2">
                        @foreach($user->servers as $server)
                            <div class="col">
                                <span class="badge text-bg-light w-100 text-start">{{ $server->name }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-body-secondary mb-0">{{ __('No servers assigned to this user.') }}</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
