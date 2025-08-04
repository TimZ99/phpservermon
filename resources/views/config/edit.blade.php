<x-app-layout>
    <x-slot name="header">
        {{ __('Configuration') }}
    </x-slot>
    <form method="post" action="{{ route('config.update') }}" class="mt-6">
        @csrf
        @method('patch')
        <div class="card">
            <div class="card-header">
                Config page
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
                            <input class="form-check-input mt-1 mb-2" type="checkbox" value="1" @checked(old('email_notifications_enabled', $email_notifications_enabled)) id="email_notifications_enabled" name="email_notifications_enabled">
                            <label for="email_notifications_enabled">Globally enable email notifications</label>
                            <x-input-error class="mt-2" :messages="$errors->get('email_notifications_enabled')" />
                        </div>

                        <label for="from_name">{{ __('Email name') }}</label>
                        <input id="from_name" name="from_name" class="form-control mt-1 mb-2" type="text" value="{{old('from_name', $from_name)}}" autocomplete="off" />
                        <x-input-error class="mt-2" :messages="$errors->get('from_name')" />

                        <label for="from_address">{{ __('Email address') }}</label>
                        <input id="from_address" name="from_address" class="form-control mt-1 mb-2" type="text" value="{{ old('from_address', $from_address) }}" autocomplete="off" />
                        <x-input-error class="mt-2" :messages="$errors->get('from_address')" />

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
                            <input class="form-check-input mt-1 mb-2" type="checkbox" value="1" @checked(old('telegram_notifications_enabled', $telegram_notifications_enabled)) id="telegram_notifications_enabled" name="telegram_notifications_enabled">
                            <label for="telegram_notifications_enabled">Globally enable Telegram notifications</label>
                            <x-input-error class="mt-2" :messages="$errors->get('telegram_notifications_enabled')" />
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