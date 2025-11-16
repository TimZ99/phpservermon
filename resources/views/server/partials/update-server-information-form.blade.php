@php
$ports = [
    80 => 'HTTP (80)', 443 => 'HTTPS (443)', 21 => 'FTP (21)', 25 => 'SMTP (25)', 465 => 'SMTP Secure (465)',
    110 => 'POP3 (110)', 995 => 'POP3 Secure (995)', 143 => 'IMAP (143)', 993 => 'IMAP over SSL (993)',
    22 => 'SSH (22)', 389 => 'LDAP (389)', 3306 => 'MySQL (3306)', 115 => 'SFTP (115)', 43 => 'WHOIS (43)',
    53 => 'BIND (53)', 3389 => 'RDP (3389)'
];

$defaultCheckSettings = $defaultCheckSettings ?? [];
$checkDefinitions = $checkDefinitions ?? [];
$resolvedSettings = old('check_settings');
if (! is_array($resolvedSettings)) {
    $resolvedSettings = $server->check_settings ?? [];
}
$resolvedSettings = array_replace_recursive($defaultCheckSettings, $resolvedSettings);

$headerLines = collect(data_get($resolvedSettings, 'Headers.input.required', []))
    ->map(function ($value, $name) {
        return $value === null || $value === '' ? $name : $name.':'.$value;
    })
    ->implode(PHP_EOL);
@endphp

<form method="post" action="{{ route('server.update', $server->id) }}">
    @csrf
    @method('patch')

    <div class="d-flex flex-column gap-4">
        <div class="card">
            <div class="card-body">
                <div class="mb-4">
                    <p class="text-uppercase text-muted small mb-1">{{ __('General') }}</p>
                    <h2 class="h4 mb-0">{{ __('Server settings') }}</h2>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">{{ __('Name') }}</label>
                        <input id="name" name="name" class="form-control" type="text" value="{{ old('name', $server->name) }}" required autofocus autocomplete="off" />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>
                    <div class="col-md-6">
                        <label for="ip" class="form-label">{{ __('IP') }}</label>
                        <input id="ip" name="ip" class="form-control" type="text" value="{{ old('ip', $server->ip) }}" required autocomplete="off" />
                        <x-input-error class="mt-2" :messages="$errors->get('ip')" />
                    </div>
                    <div class="col-md-6">
                        <label for="popular_ports" class="form-label">{{ __('Port') }}</label>
                        <select id="popular_ports" name="popular_ports" class="form-select">
                            <option @empty(old('port', $server->port)) selected @endempty disabled>{{ __('Select a port') }}</option>
                            <option @if (in_array(old('port', $server->port), array_keys($ports))) selected @endif value="custom">{{ __('Custom port') }}</option>
                            <optgroup label="{{ __('Popular ports') }}">
                                @foreach ($ports as $value => $label)
                                <option @if (old('port', $server->port) == $value) selected @endif value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('popular_ports')" />
                    </div>
                    <div class="col-md-6 d-flex flex-column">
                        <label for="port" class="form-label d-none">{{ __('Custom port') }}</label>
                        <input id="port" name="port" class="form-control d-none" type="number" value="{{ old('port', $server->port) }}" autocomplete="off" />
                        <x-input-error class="mt-2" :messages="$errors->get('port')" />
                    </div>
                    <div class="col-12">
                        <label for="users" class="form-label">{{ __('Users') }}</label>
                        <select class="form-select" id="users" name="users[]" multiple>
                            @foreach ($users as $user)
                            <option
                                value="{{ $user->id }}"
                                @if(in_array($user->id, ($server->users ?? collect())->pluck('id')->toArray())) selected @endif
                            >
                                {{ $user->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

    <div class="card">
        <div class="card-body">
                <div class="mb-4">
                    <p class="text-uppercase text-muted small mb-1">{{ __('Checks') }}</p>
                    <h2 class="h4 mb-0">{{ __('Check settings') }}</h2>
                    <p class="text-body-secondary small mb-0">{{ __('Enable or disable individual checks and tweak their thresholds. Checks run in the order shown.') }}</p>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title mb-1">{{ __('Status code') }}</h5>
                                <p class="text-body-secondary small mb-0">{{ data_get($checkDefinitions, 'StatusCode.description') }}</p>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="check_settings[StatusCode][enabled]" value="0">
                                @php $statusCodeEnabled = old('check_settings.StatusCode.enabled', data_get($resolvedSettings, 'StatusCode.enabled', true)); @endphp
                                <input class="form-check-input" id="check-status-code" type="checkbox" name="check_settings[StatusCode][enabled]" value="1" @checked((bool) $statusCodeEnabled)>
                            </div>
                        </div>
                    </div>
                </div>
                    </div>

                    <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title mb-1">{{ __('SSL active') }}</h5>
                                <p class="text-body-secondary small mb-0">{{ data_get($checkDefinitions, 'SSL_active.description') }}</p>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="check_settings[SSL_active][enabled]" value="0">
                                @php $sslActiveEnabled = old('check_settings.SSL_active.enabled', data_get($resolvedSettings, 'SSL_active.enabled', true)); @endphp
                                <input class="form-check-input" id="check-ssl-active" type="checkbox" name="check_settings[SSL_active][enabled]" value="1" @checked((bool) $sslActiveEnabled)>
                            </div>
                        </div>
                    </div>
                </div>
                    </div>

                    <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title mb-1">{{ __('SSL certificate validity') }}</h5>
                                <p class="text-body-secondary small mb-0">{{ data_get($checkDefinitions, 'SSL_certificate_valid.description') }}</p>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="check_settings[SSL_certificate_valid][enabled]" value="0">
                                @php $sslValidEnabled = old('check_settings.SSL_certificate_valid.enabled', data_get($resolvedSettings, 'SSL_certificate_valid.enabled', true)); @endphp
                                <input class="form-check-input" id="check-ssl-valid" type="checkbox" name="check_settings[SSL_certificate_valid][enabled]" value="1" @checked((bool) $sslValidEnabled)>
                            </div>
                        </div>
                    </div>
                </div>
                    </div>

                    <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title mb-1">{{ __('SSL expiration window') }}</h5>
                                <p class="text-body-secondary small mb-2">{{ data_get($checkDefinitions, 'SSL_expiration.description') }}</p>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="check_settings[SSL_expiration][enabled]" value="0">
                                @php $sslExpiryEnabled = old('check_settings.SSL_expiration.enabled', data_get($resolvedSettings, 'SSL_expiration.enabled', true)); @endphp
                                <input class="form-check-input" id="check-ssl-expiration" type="checkbox" name="check_settings[SSL_expiration][enabled]" value="1" @checked((bool) $sslExpiryEnabled)>
                            </div>
                        </div>
                        <label for="ssl-expiration-days" class="form-label mt-3">{{ __('Warn when certificate expires within (days)') }}</label>
                        @php $sslDays = old('check_settings.SSL_expiration.days', data_get($resolvedSettings, 'SSL_expiration.input.days', 5)); @endphp
                        <input id="ssl-expiration-days" type="number" min="1" max="365" class="form-control" name="check_settings[SSL_expiration][days]" value="{{ $sslDays }}">
                        <x-input-error class="mt-2" :messages="$errors->get('check_settings.SSL_expiration.days')" />
                    </div>
                </div>
                    </div>

                    <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title mb-1">{{ __('Content regex') }}</h5>
                                <p class="text-body-secondary small mb-2">{{ data_get($checkDefinitions, 'ContentRegex.description') }}</p>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="check_settings[ContentRegex][enabled]" value="0">
                                @php $contentRegexEnabled = old('check_settings.ContentRegex.enabled', data_get($resolvedSettings, 'ContentRegex.enabled', false)); @endphp
                                <input class="form-check-input" id="check-content-regex" type="checkbox" name="check_settings[ContentRegex][enabled]" value="1" @checked((bool) $contentRegexEnabled)>
                            </div>
                        </div>
                        <label for="content-regex-pattern" class="form-label mt-3">{{ __('Pattern') }}</label>
                        <input id="content-regex-pattern" type="text" class="form-control" name="check_settings[ContentRegex][pattern]" placeholder="/example/i" value="{{ old('check_settings.ContentRegex.pattern', data_get($resolvedSettings, 'ContentRegex.input.pattern', '')) }}">
                        <x-input-error class="mt-2" :messages="$errors->get('check_settings.ContentRegex.pattern')" />
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title mb-1">{{ __('Latency thresholds (ms)') }}</h5>
                                <p class="text-body-secondary small mb-2">{{ data_get($checkDefinitions, 'Latency.description') }}</p>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="check_settings[Latency][enabled]" value="0">
                                @php $latencyEnabled = old('check_settings.Latency.enabled', data_get($resolvedSettings, 'Latency.enabled', true)); @endphp
                                <input class="form-check-input" id="check-latency" type="checkbox" name="check_settings[Latency][enabled]" value="1" @checked((bool) $latencyEnabled)>
                            </div>
                        </div>
                        <div class="row mt-3 g-3">
                            <div class="col">
                                <label for="latency-warning" class="form-label">{{ __('Warning') }}</label>
                                <input id="latency-warning" type="number" min="10" max="120000" class="form-control" name="check_settings[Latency][warning_ms]" value="{{ old('check_settings.Latency.warning_ms', data_get($resolvedSettings, 'Latency.input.warning_ms', 600)) }}">
                            </div>
                            <div class="col">
                                <label for="latency-fail" class="form-label">{{ __('Failure') }}</label>
                                <input id="latency-fail" type="number" min="10" max="120000" class="form-control" name="check_settings[Latency][fail_ms]" value="{{ old('check_settings.Latency.fail_ms', data_get($resolvedSettings, 'Latency.input.fail_ms', 1500)) }}">
                            </div>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('check_settings.Latency.warning_ms')" />
                        <x-input-error class="mt-2" :messages="$errors->get('check_settings.Latency.fail_ms')" />
                    </div>
                </div>
                    </div>

                    <div class="col-12">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title mb-1">{{ __('Response headers') }}</h5>
                                <p class="text-body-secondary small mb-2">{{ data_get($checkDefinitions, 'Headers.description') }}</p>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="check_settings[Headers][enabled]" value="0">
                                @php $headersEnabled = old('check_settings.Headers.enabled', data_get($resolvedSettings, 'Headers.enabled', false)); @endphp
                                <input class="form-check-input" id="check-headers" type="checkbox" name="check_settings[Headers][enabled]" value="1" @checked((bool) $headersEnabled)>
                            </div>
                        </div>
                        <label for="headers-required" class="form-label mt-3">{{ __('Required headers (one per line, Header:Value)') }}</label>
                        <textarea id="headers-required" class="form-control" rows="4" name="check_settings[Headers][required]" placeholder="Cache-Control:no-store&#10;X-Frame-Options:DENY">{{ old('check_settings.Headers.required', $headerLines) }}</textarea>
                        <div class="form-text">{{ __('Leave value empty to only assert presence. Prefix with /regex/ for pattern checks.') }}</div>
                        <x-input-error class="mt-2" :messages="$errors->get('check_settings.Headers.required')" />
                    </div>
                </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <div class="card mt-4">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <p class="text-uppercase text-muted small mb-1">{{ __('Save changes') }}</p>
                    <p class="mb-0 text-body-secondary">{{ __('Any changes you made above are applied immediately after saving.') }}</p>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>

                    @if (session('status') === 'server-updated')
                    <p
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 2500)"
                        class="text-success mb-0"
                    >
                        {{ __('Server updated successfully.') }}
                    </p>
                    @endif
                </div>
            </div>
        </div>
</form>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const popularPortsSelect = document.getElementById('popular_ports');
        const portInput = document.getElementById('port');
        const portLabel = document.querySelector('label[for="port"]');

        popularPortsSelect.addEventListener("change", function() {
            if (popularPortsSelect.value === 'custom') {
                portInput.classList.remove('d-none');
                portLabel.classList.remove('d-none');
                portInput.focus();
            } else {
                portInput.value = popularPortsSelect.value;
                portInput.classList.add('d-none');
                portLabel.classList.add('d-none');
            }
        });

        popularPortsSelect.dispatchEvent(new Event('change'));
    });
</script>
