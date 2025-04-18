<section>
    <header>
        <h2>{{ __('General settings') }}</h2>
    </header>
    <form method="post" action="{{ route('user.update', ['user' => $user]) }}" class="mt-6">
        @csrf
        @method('patch')

        <label for="name">{{ __('Name') }}</label>
        <input id="name" name="name" class="form-control mb-2" type="text" class="mt-1" value="{{old('name', $user->name)}}" required autofocus autocomplete="off" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />

        <label for="email">{{ __('Email') }}</label>
        <input id="email" name="email" class="form-control mb-2" type="email" class="mt-1" value="{{old('email', $user->email)}}" required autocomplete="off" />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />

        <input id="admin" name="admin" class="form-check-input mb-2" type="checkbox" class="mt-1" value="1" autocomplete="off" @if (old('admin', $user->admin)) checked @endif>
        <label for="admin" class="form-check-label">{{ __('Admin') }}</label>
        <x-input-error class="mt-2" :messages="$errors->get('admin')" />
        <br>
        <input id="suspended" name="suspended" class="form-check-input mb-2" type="checkbox" class="mt-1" value="1" autocomplete="off" @if (old('suspended', $user->suspended)) checked @endif/>
        <label for="suspended" class="form-check-label">{{ __('Suspended') }}</label>
        <x-input-error class="mt-2" :messages="$errors->get('suspended')" />
        
        <br><br>
        <label>Scopes</label><br>

        @foreach ($valid_scopes as $scope)
            <input id="scope-{{$loop->index}}" name="scopes[]" class="form-check-input mb-2" type="checkbox" class="mt-1" value="{{ $scope }}" autocomplete="off" @if (in_array($scope, $user->scopes ?? [])) checked @endif/>
            <label for="scope-{{$loop->index}}" class="form-check-label">{{ $scope }}</label>
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
