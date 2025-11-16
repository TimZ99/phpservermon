<form method="post" action="{{ route('user.update', ['user' => $user]) }}">
    @csrf
    @method('patch')

    <div class="card">
        <div class="card-body">
            <div class="mb-4">
                <p class="text-uppercase text-muted small mb-1">{{ __('General') }}</p>
                <h2 class="h4 mb-0">{{ __('User settings') }}</h2>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">{{ __('Name') }}</label>
                    <input id="name" name="name" class="form-control" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">{{ __('Email') }}</label>
                    <input id="email" name="email" class="form-control" type="email" value="{{ old('email', $user->email) }}" required autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">{{ __('Phone') }}</label>
                    <input id="phone" name="phone" class="form-control" type="tel" value="{{ old('phone', $user->phone) }}" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                </div>

                <div class="col-md-6">
                    <label for="telegram_user_id" class="form-label">{{ __('Telegram user id') }}</label>
                    <input id="telegram_user_id" name="telegram_user_id" class="form-control" type="number" value="{{ old('telegram_user_id', $user->telegram_user_id) }}" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('telegram_user_id')" />
                </div>

                <div class="col-12">
                    <label for="servers" class="form-label">{{ __('Servers') }}</label>
                    <select class="form-select" id="servers" name="servers[]" multiple>
                        @foreach ($servers as $server)
                        <option
                            value="{{ $server->id }}"
                            @if(in_array($server->id, ($user->servers ?? collect())->pluck('id')->toArray())) selected @endif
                        >
                            {{ $server->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <p class="text-uppercase text-muted small mb-1">{{ __('Access control') }}</p>
                <div class="form-check form-switch mb-3">
                    <input id="suspended" name="suspended" class="form-check-input" type="checkbox" value="1" autocomplete="off" @checked(old('suspended', $user->suspended)) />
                    <label for="suspended" class="form-check-label">{{ __('Suspended') }}</label>
                    <x-input-error class="mt-2" :messages="$errors->get('suspended')" />
                </div>

                <label class="form-label">{{ __('Scopes') }}</label>
                <x-input-error class="mt-2" :messages="$errors->get('lastuser:editscope')" />
                <div class="row row-cols-1 row-cols-md-2 g-2">
                    @foreach ($validScopes as $scope)
                        <div class="col">
                            <div class="form-check">
                                <input
                                    id="scope-{{ $loop->index }}"
                                    name="scopes[]"
                                    type="checkbox"
                                    class="form-check-input"
                                    value="{{ $scope }}"
                                    autocomplete="off"
                                    @checked(in_array($scope, old('scopes', $user->scopes ?? [])))
                                />
                                <label for="scope-{{ $loop->index }}" class="form-check-label">
                                    {{ $scope }}
                                </label>
                            </div>
                            <x-input-error class="mt-1" :messages="$errors->get('scopes.'.$loop->index)" />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <p class="text-uppercase text-muted small mb-1">{{ __('Save changes') }}</p>
                <p class="mb-0 text-body-secondary">{{ __('Persist the updated profile, scopes, and server assignments.') }}</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <x-primary-button>{{ __('Save') }}</x-primary-button>
                <x-input-error class="mt-2" :messages="$errors->get('general')" />
            </div>
        </div>
    </div>
</form>
