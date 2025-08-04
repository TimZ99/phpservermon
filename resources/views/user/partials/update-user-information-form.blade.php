<section>
    <header>
        <h2>{{ __('General settings') }}</h2>
    </header>
    <form method="post" action="{{ route('user.update', ['user' => $user]) }}" class="mt-6">
        @csrf
        @method('patch')

        <label for="name">{{ __('Name') }}</label>
        <input id="name" name="name" class="form-control mt-1 mb-2" type="text" value="{{old('name', $user->name)}}" required autofocus autocomplete="off" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />

        <label for="email">{{ __('Email') }}</label>
        <input id="email" name="email" class="form-control mb-2" type="email" class="mt-1" value="{{old('email', $user->email)}}" required autocomplete="off" />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />

        <label for="phone">{{ __('Phone') }}</label>
        <input id="phone" name="phone" class="form-control mb-2" type="tel" class="mt-1" value="{{old('phone', $user->phone)}}" autocomplete="off" />
        <x-input-error class="mt-2" :messages="$errors->get('phone')" />

        <label for="telegram_user_id">{{ __('Telegram user id') }}</label>
        <input id="telegram_user_id" name="telegram_user_id" class="form-control mb-2" type="number" class="mt-1" value="{{old('telegram_user_id', $user->telegram_user_id)}}" autocomplete="off" />
        <x-input-error class="mt-2" :messages="$errors->get('telegram_user_id')" />

        <input id="suspended" name="suspended" class="form-check-input mb-2" type="checkbox" class="mt-1" value="1" autocomplete="off" @if (old('suspended', $user->suspended)) checked @endif/>
        <label for="suspended" class="form-check-label">{{ __('Suspended') }}</label>
        <x-input-error class="mt-2" :messages="$errors->get('suspended')" />

        <br><br>
        <label>Scopes</label><br>
        <x-input-error class="mt-2" :messages="$errors->get('lastuser:editscope')" />
        @foreach ($validScopes as $scope)
        <input
            id="scope-{{ $loop->index }}"
            name="scopes[]"
            type="checkbox"
            class="form-check-input scope-checkbox mb-2 mt-1"
            value="{{ $scope }}"
            autocomplete="off"
            @if (in_array($scope, old('scopes', $user->scopes ?? []))) checked @endif />
        <label for="scope-{{ $loop->index }}" class="form-check-label">
            {{ $scope }}
        </label>
        <x-input-error class="mt-2" :messages="$errors->get('scopes.'.$loop->index)" />
        <br>
        @endforeach
        <br>

        <label for="servers">{{ __('Servers') }}</label>
        <select class="form-select mb-2" id="users" name="servers[]" multiple>
            @foreach ($servers as $server)
            <option
                value="{{ $server->id }}"
                @if(in_array($server->id, $user->servers->pluck('id')->toArray())) selected @endif
                >
                {{ $server->name }}
            </option>
            @endforeach
        </select>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            <x-input-error class="mt-2" :messages="$errors->get('general')" />
        </div>
    </form>
</section>