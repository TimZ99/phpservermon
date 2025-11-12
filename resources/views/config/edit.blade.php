<x-app-layout>
    <x-slot name="header">
        {{ __('Configuration') }}
    </x-slot>
    <form method="post" action="{{ route('config.update') }}" class="mt-6">
        @csrf
        @method('patch')
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>{{ __('Config page') }}</span>
                <span id="heartbeat-indicator" class="badge bg-secondary">
                    @if ($queue_connection !== 'database')
                        {{ __('Queue driver: :driver', ['driver' => $queue_connection]) }}
                    @else
                        {{ __('Checking heartbeat...') }}
                    @endif
                </span>
            </div>
            <div class="card-body">

                <label for="locale">{{ __('Locale') }}</label>
                <input id="locale" name="locale" class="form-control mt-1 mb-2" type="text" value="{{ old('locale', $locale) }}" required autofocus autocomplete="off" />
                <x-input-error class="mt-2" :messages="$errors->get('locale')" />

                <label for="timezone">{{ __('Timezone') }}</label>
                <select id="timezone" name="timezone" class="form-select mb-2" required>
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

                <label for="check_history_retention_days" class="mt-3">{{ __('Check history retention (days)') }}</label>
                <input id="check_history_retention_days" name="check_history_retention_days" class="form-control mt-1 mb-2" type="number" min="1" max="365" value="{{ old('check_history_retention_days', $check_history_retention_days) }}" required />
                <x-input-error class="mt-2" :messages="$errors->get('check_history_retention_days')" />

                <div class="flex items-center gap-4">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                    @if(session('success'))
                    <p class="pt-4" x-data="{ show: true }" x-show="show">
                        {{ __('Config updated successfully.') }}
                    </p>
                    @endif
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        Email
                    </div>
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input mt-1 mb-2" type="checkbox" value="1" @checked(old('email_global_enabled', $email_global_enabled)) id="email_global_enabled" name="email_global_enabled">
                            <label for="email_global_enabled">Globally enable email notifications</label>
                            <x-input-error class="mt-2" :messages="$errors->get('email_global_enabled')" />
                        </div>

                        <label for="email_from_name">{{ __('Email name') }}</label>
                        <input id="email_from_name" name="email_from_name" class="form-control mt-1 mb-2" type="text" value="{{old('email_from_name', $email_from_name)}}" autocomplete="off" />
                        <x-input-error class="mt-2" :messages="$errors->get('email_from_name')" />

                        <label for="email_from_address">{{ __('Email address') }}</label>
                        <input id="email_from_address" name="email_from_address" class="form-control mt-1 mb-2" type="text" value="{{ old('email_from_address', $email_from_address) }}" autocomplete="off" />
                        <x-input-error class="mt-2" :messages="$errors->get('email_from_address')" />

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                            @if(session('success'))
                            <p class="pt-4" x-data="{ show: true }" x-show="show">
                                {{ __('Config updated successfully.') }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        Telegram
                    </div>
                    <div class="card-body">

                        <div class="form-check">
                            <input class="form-check-input mt-1 mb-2" type="checkbox" value="1" @checked(old('telegram_global_enabled', $telegram_global_enabled)) id="telegram_global_enabled" name="telegram_global_enabled">
                            <label for="telegram_global_enabled">Globally enable Telegram notifications</label>
                            <x-input-error class="mt-2" :messages="$errors->get('telegram_global_enabled')" />
                        </div>

                        <label for="telegram_bot_token">{{ __('Telegram Bot Token') }}</label>
                        <input id="telegram_bot_token" name="telegram_bot_token" class="form-control mt-1 mb-2" type="text" value="{{ old('telegram_bot_token', $telegram_bot_token) }}" autocomplete="off" />
                        <x-input-error class="mt-2" :messages="$errors->get('telegram_bot_token')" />

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                            @if(session('success'))
                            <p class="pt-4" x-data="{ show: true }" x-show="show">
                                {{ __('Config updated successfully.') }}
                            </p>
                            @endif
                        </div>
                    </div>
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
