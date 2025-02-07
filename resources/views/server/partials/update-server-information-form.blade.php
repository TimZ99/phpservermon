<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('General settings') }}
        </h2>
    </header>
    <form method="post" action="{{ route('server.update', $server->id) }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <label for="name">{{ __('Name') }}</label>
        <input id="name" name="name" class="form-control mb-2" type="text" class="mt-1" value="{{old('name', $server->name)}}" required autofocus autocomplete="off" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />

        <label for="ip">{{ __('IP') }}</label>
        <input id="ip" name="ip" class="form-control mb-2" type="text" class="mt-1" value="{{old('ip', $server->ip)}}" required autocomplete="off" />
        <x-input-error class="mt-2" :messages="$errors->get('ip')" />

        <label for="port">{{ __('Port') }}</label>
        <input id="port" name="port" class="form-control mb-2" type="text" class="mt-1" value="{{old('port', $server->port)}}" required autocomplete="off" />
        <x-input-error class="mt-2" :messages="$errors->get('port')" />

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'server-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
