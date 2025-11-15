<x-app-layout>
    <x-slot name="header">
        {{ __('Configuration') }}
    </x-slot>
    <form method="post" action="{{ route('config.update') }}">
        @csrf
        @method('patch')
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    <div>
                        <p class="text-uppercase text-muted small mb-1">{{ __('General') }}</p>
                        <h2 class="h4 mb-0">{{ __('Configuration') }}</h2>
                    </div>
                    <span id="heartbeat-indicator" class="badge text-bg-secondary">
                        @if ($queue_connection !== 'database')
                            {{ __('Queue driver: :driver', ['driver' => $queue_connection]) }}
                        @else
                            {{ __('Checking heartbeat...') }}
                        @endif
                    </span>
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <label for="locale" class="form-label">{{ __('Locale') }}</label>
                        <input id="locale" name="locale" class="form-control" type="text" value="{{ old('locale', $locale) }}" required autofocus autocomplete="off" />
                        <x-input-error class="mt-2" :messages="$errors->get('locale')" />
                    </div>
                    <div class="col-lg-6">
                        <label for="timezone" class="form-label">{{ __('Timezone') }}</label>
                        <select id="timezone" name="timezone" class="form-select" required>
                    <option @empty(old('timezone', $timezone)) selected @endempty disabled>{{ __('Select a timezone') }}</option>
                    <optgroup label="{{ __('UTC') }}">
                        @foreach (DateTimeZone::listIdentifiers(DateTimeZone::UTC) as $tz)
                        <option @if (old('timezone', $timezone)==$tz) selected @endif value="{{ $tz }}">{{ $tz }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="{{ __('Africa') }}">
                        @foreach (DateTimeZone::listIdentifiers(DateTimeZone::AFRICA) as $tz)
                        <option @if (old('timezone', $timezone)==$tz) selected @endif value="{{ $tz }}">{{ $tz }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="{{ __('America') }}">
                        @foreach (DateTimeZone::listIdentifiers(DateTimeZone::AMERICA) as $tz)
                        <option @if (old('timezone', $timezone)==$tz) selected @endif value="{{ $tz }}">{{ $tz }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="{{ __('Antarctica') }}">
                        @foreach (DateTimeZone::listIdentifiers(DateTimeZone::ANTARCTICA) as $tz)
                        <option @if (old('timezone', $timezone)==$tz) selected @endif value="{{ $tz }}">{{ $tz }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="{{ __('Arctic') }}">
                        @foreach (DateTimeZone::listIdentifiers(DateTimeZone::ARCTIC) as $tz)
                        <option @if (old('timezone', $timezone)==$tz) selected @endif value="{{ $tz }}">{{ $tz }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="{{ __('Asia') }}">
                        @foreach (DateTimeZone::listIdentifiers(DateTimeZone::ASIA) as $tz)
                        <option @if (old('timezone', $timezone)==$tz) selected @endif value="{{ $tz }}">{{ $tz }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="{{ __('Atlantic') }}">
                        @foreach (DateTimeZone::listIdentifiers(DateTimeZone::ATLANTIC) as $tz)
                        <option @if (old('timezone', $timezone)==$tz) selected @endif value="{{ $tz }}">{{ $tz }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="{{ __('Australia') }}">
                        @foreach (DateTimeZone::listIdentifiers(DateTimeZone::AUSTRALIA) as $tz)
                        <option @if (old('timezone', $timezone)==$tz) selected @endif value="{{ $tz }}">{{ $tz }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="{{ __('Europe') }}">
                        @foreach (DateTimeZone::listIdentifiers(DateTimeZone::EUROPE) as $tz)
                        <option @if (old('timezone', $timezone)==$tz) selected @endif value="{{ $tz }}">{{ $tz }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="{{ __('Indian') }}">
                        @foreach (DateTimeZone::listIdentifiers(DateTimeZone::INDIAN) as $tz)
                        <option @if (old('timezone', $timezone)==$tz) selected @endif value="{{ $tz }}">{{ $tz }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="{{ __('Pacific') }}">
                        @foreach (DateTimeZone::listIdentifiers(DateTimeZone::PACIFIC) as $tz)
                        <option @if (old('timezone', $timezone)==$tz) selected @endif value="{{ $tz }}">{{ $tz }}</option>
                        @endforeach
                    </optgroup>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
                    </div>
                    <div class="col-lg-6">
                        <label for="check_history_retention_days" class="form-label">{{ __('Check history retention (days)') }}</label>
                        <input id="check_history_retention_days" name="check_history_retention_days" class="form-control" type="number" min="1" max="365" value="{{ old('check_history_retention_days', $check_history_retention_days) }}" required />
                        <x-input-error class="mt-2" :messages="$errors->get('check_history_retention_days')" />
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4 g-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-4">
                            <p class="text-uppercase text-muted small mb-1">{{ __('Email') }}</p>
                            <h2 class="h5 mb-0">{{ __('Email notifications') }}</h2>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" @checked(old('email_global_enabled', $email_global_enabled)) id="email_global_enabled" name="email_global_enabled">
                            <label for="email_global_enabled">{{ __('Globally enable email notifications') }}</label>
                            <x-input-error class="mt-2" :messages="$errors->get('email_global_enabled')" />
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="email_from_name" class="form-label">{{ __('Email name') }}</label>
                                <input id="email_from_name" name="email_from_name" class="form-control" type="text" value="{{old('email_from_name', $email_from_name)}}" autocomplete="off" />
                                <x-input-error class="mt-2" :messages="$errors->get('email_from_name')" />
                            </div>
                            <div class="col-12">
                                <label for="email_from_address" class="form-label">{{ __('Email address') }}</label>
                                <input id="email_from_address" name="email_from_address" class="form-control" type="text" value="{{ old('email_from_address', $email_from_address) }}" autocomplete="off" />
                                <x-input-error class="mt-2" :messages="$errors->get('email_from_address')" />
                            </div>
                            <div class="col-md-6">
                                <label for="email_username" class="form-label">{{ __('Username') }}</label>
                                <input id="email_username" name="email_username" class="form-control" type="text" value="{{ old('email_username', $email_username) }}" autocomplete="off" />
                                <x-input-error class="mt-2" :messages="$errors->get('email_username')" />
                            </div>
                            <div class="col-md-6">
                                <label for="email_password" class="form-label">{{ __('Password') }}</label>
                                <input id="email_password" name="email_password" class="form-control" type="password" value="{{ old('email_password') }}" autocomplete="off" />
                                <x-input-error class="mt-2" :messages="$errors->get('email_password')" />
                            </div>
                            <div class="col-md-6">
                                <label for="email_host" class="form-label">{{ __('Host') }}</label>
                                <input id="email_host" name="email_host" class="form-control" type="text" value="{{ old('email_host', $email_host) }}" placeholder="smtp.example.com" />
                                <x-input-error class="mt-2" :messages="$errors->get('email_host')" />
                            </div>
                            <div class="col-md-6">
                                <label for="email_port" class="form-label">{{ __('Port') }}</label>
                                <input id="email_port" name="email_port" class="form-control" type="number" value="{{ old('email_port', $email_port) }}" autocomplete="off" />
                                <x-input-error class="mt-2" :messages="$errors->get('email_port')" />
                            </div>
                            <div class="col-md-6">
                                <label for="email_encryption" class="form-label">{{ __('Encryption') }}</label>
                                <select id="email_encryption" name="email_encryption" class="form-select">
                                    <option value="">{{ __('None') }}</option>
                                    <option value="ssl" @selected(old('email_encryption', $email_encryption) === 'ssl')>SSL</option>
                                    <option value="tls" @selected(old('email_encryption', $email_encryption) === 'tls')>TLS</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('email_encryption')" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-4">
                            <p class="text-uppercase text-muted small mb-1">{{ __('Telegram') }}</p>
                            <h2 class="h5 mb-0">{{ __('Telegram notifications') }}</h2>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" @checked(old('telegram_global_enabled', $telegram_global_enabled)) id="telegram_global_enabled" name="telegram_global_enabled">
                            <label for="telegram_global_enabled">{{ __('Globally enable Telegram notifications') }}</label>
                            <x-input-error class="mt-2" :messages="$errors->get('telegram_global_enabled')" />
                        </div>
                        <label for="telegram_bot_token" class="form-label">{{ __('Telegram Bot Token') }}</label>
                        <input id="telegram_bot_token" name="telegram_bot_token" class="form-control" type="text" value="{{ old('telegram_bot_token', $telegram_bot_token) }}" autocomplete="off" />
                        <x-input-error class="mt-2" :messages="$errors->get('telegram_bot_token')" />
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <p class="text-uppercase text-muted small mb-1">{{ __('Save changes') }}</p>
                    <p class="mb-0 text-body-secondary">{{ __('Updates apply globally across the application once saved.') }}</p>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                    @if(session('success'))
                    <p
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 2500)"
                        class="text-success mb-0"
                    >
                        {{ __('Config updated successfully.') }}
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </form>
 </x-app-layout>
 <script>
    document.addEventListener('DOMContentLoaded', () => {
        const indicator = document.getElementById('heartbeat-indicator');
        if (! indicator) return;

        const queueDriver = '{{ $queue_connection }}';
        if (queueDriver !== 'database') {
            indicator.classList.remove('bg-secondary');
            indicator.classList.add('bg-primary');
            indicator.textContent = `{{ __('Queue driver: :driver') }}`.replace(':driver', queueDriver);
            return;
        }

        const pollHeartbeat = () => fetch('{{ route('config.heartbeat') }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(response => response.json())
            .then(data => {
                indicator.classList.remove('bg-secondary', 'bg-success', 'bg-danger', 'bg-warning', 'bg-primary');

                if (data.alive) {
                    indicator.classList.add('bg-success');
                    indicator.textContent = '{{ __('Heartbeat alive – queue driver: database') }}';
                } else {
                    indicator.classList.add('bg-warning');
                    indicator.textContent = '{{ __('Heartbeat not found – using queue driver sync instead') }}';
                }
            })
            .catch(() => {
                indicator.classList.remove('bg-secondary');
                indicator.classList.add('bg-warning');
                indicator.textContent = '{{ __('Heartbeat status unknown') }}';
            });

        pollHeartbeat();
        setInterval(pollHeartbeat, 300000);
    });
 </script>
