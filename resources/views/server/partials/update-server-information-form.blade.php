@php
    $ports = [
        80 => 'HTTP (80)', 443 => 'HTTPS (443)', 21 => 'FTP (21)', 25 => 'SMTP (25)', 465 => 'SMTP Secure (465)',
        110 => 'POP3 (110)', 995 => 'POP3 Secure (995)', 143 => 'IMAP (143)', 993 => 'IMAP over SSL (993)',
        22 => 'SSH (22)', 389 => 'LDAP (389)', 3306 => 'MySQL (3306)', 115 => 'SFTP (115)', 43 => 'WHOIS (43)',
        53 => 'BIND (53)', 3389 => 'RDP (3389)'
    ];
@endphp

<section>
    <header>
        <h2>{{ __('General settings') }}</h2>
    </header>
    <form method="post" action="{{ route('server.update', $server->id) }}" class="mt-6">
        @csrf
        @method('patch')

        <label for="name">{{ __('Name') }}</label>
        <input id="name" name="name" class="form-control mb-2" type="text" class="mt-1" value="{{old('name', $server->name)}}" required autofocus autocomplete="off" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />

        <label for="ip">{{ __('IP') }}</label>
        <input id="ip" name="ip" class="form-control mb-2" type="text" class="mt-1" value="{{ old('ip', $server->ip) }}" required autocomplete="off" />
        <x-input-error class="mt-2" :messages="$errors->get('ip')" />
        
        <label for="popular_ports">{{ __('Port') }}</label>
        <select id="popular_ports" name="popular_ports" class="form-select mb-2">
            <option @empty(old('port', $server->port)) selected @endempty disabled>{{ __('Select a port') }}</option>
            <option @if (in_array(old('port', $server->port), array_keys($ports))) selected @endif value="custom">{{ __('Custom port') }}</option>
            <optgroup label="{{ __('Popular ports') }}">
                @foreach ($ports as $value => $label)
                    <option @if (old('port', $server->port) == $value) selected @endif value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </optgroup>
        </select>

    <div class="form-group">
        <label for="users">{{ __('Users') }}</label>
        <select class="form-select mb-2" id="users" name="users[]" multiple>
            @foreach ($users as $user)
                <option value="{{ $user->id }}"> {{ $user->name }}</option>
            @endforeach
        </select>
    </div>

        <label for="port" class="d-none">{{ __('Custom port') }}</label>
        <input id="port" name="port" class="form-control mb-2 d-none" type="number" value="{{ old('port', $server->port) }}" required autocomplete="off" />
        <x-input-error class="mt-2" :messages="$errors->get('port')" />

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'server-updated')
                <p x-data="{ show: true }" x-show="show">
                    {{ __('Server updated successfully.') }}
                </p>
            @endif
        </div>
    </form>
</section>
