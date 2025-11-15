@php
    $statusMap = [
        'success' => ['label' => __('Success'), 'class' => 'badge text-bg-success'],
        'warning' => ['label' => __('Warning'), 'class' => 'badge text-bg-warning'],
        'fail' => ['label' => __('Fail'), 'class' => 'badge text-bg-danger'],
        'danger' => ['label' => __('Fail'), 'class' => 'badge text-bg-danger'],
        'error' => ['label' => __('Fail'), 'class' => 'badge text-bg-danger'],
        'unknown' => ['label' => __('Unknown'), 'class' => 'badge text-bg-secondary'],
    ];
    $overall = strtolower($server->overall_status ?? 'unknown');
    $overallMeta = $statusMap[$overall] ?? $statusMap['unknown'];
    $timezone = config('app.timezone', 'UTC');
    $lastCheckedAt = $server->last_checked_at
        ? $server->last_checked_at->timezone($timezone)->format('M j, Y H:i:s')
        : __('Never');
    $checkSettings = $checkSettings ?? [];
@endphp

<x-app-layout>
    <x-slot name="header">
        {{ __('Server details') }}
    </x-slot>

    @if (! empty($activeRunId))
    <div class="alert alert-info text-bg-info d-flex align-items-center justify-content-between" role="alert">
        <div class="d-flex align-items-center">
            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
            <div>
                <div class="fw-semibold">{{ __('Checks running...') }}</div>
                <div class="small mb-0">{{ __('Monitoring run ID: :id. This page refreshes automatically.', ['id' => $activeRunId]) }}</div>
            </div>
        </div>
        <button class="btn btn-sm btn-outline-light" onclick="window.location.reload()">{{ __('Refresh now') }}</button>
    </div>
    <script>
        setTimeout(() => window.location.reload(), 5000);
    </script>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-4 align-items-center">
                <div class="col-12 col-lg-6">
                    <div>
                        <p class="text-uppercase text-muted small mb-1">{{ __('Overview') }}</p>
                        <h2 class="h4 mb-0">{{ __('Server details') }}</h2>
                    </div>
                </div>
                <div class="col-12 col-lg-6 d-flex justify-content-lg-end gap-2">
                    @can('manage', $server)
                    <a href="{{ route('server.edit', $server->id) }}" class="btn btn-sm btn-outline-primary">
                        {{ __('Edit server') }}
                    </a>
                    @endcan
                    @can('check', $server)
                    <a href="{{ route('server.runChecks', $server->id) }}" class="btn btn-sm btn-outline-secondary">
                        {{ __('Run tests') }}
                    </a>
                    @endcan
                </div>
            </div>
            <div class="row g-4 mt-2">
                <div class="col-md-8">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">{{ __('Server ID') }}</dt>
                        <dd class="col-sm-8">{{ $server->id }}</dd>

                        <dt class="col-sm-4">{{ __('Name') }}</dt>
                        <dd class="col-sm-8">{{ $server->name }}</dd>

                        <dt class="col-sm-4">{{ __('IP / Host') }}</dt>
                        <dd class="col-sm-8">{{ $server->ip }}</dd>

                        <dt class="col-sm-4">{{ __('Port') }}</dt>
                        <dd class="col-sm-8">{{ $server->port ?? __('Default') }}</dd>

                        <dt class="col-sm-4">{{ __('Users') }}</dt>
                        <dd class="col-sm-8">
                            @forelse($server->users as $user)
                                <span class="badge text-bg-light me-1 mb-1">{{ $user->name }}</span>
                            @empty
                                <span class="text-muted">{{ __('No users attached') }}</span>
                            @endforelse
                        </dd>
                    </dl>
                </div>
                <div class="col-md-4 mt-0 mb-auto">
                    <div class="p-3 rounded border bg-secondary text-white w-100">
                        <p class="mb-2 text-uppercase fw-bold">{{ __('Overall status') }}</p>
                        <div class="d-flex align-items-center mb-3">
                            <span class="{{ $overallMeta['class'] }} me-3">{{ $overallMeta['label'] }}</span>
                            <div>
                                <div class="small">{{ __('Last check') }}</div>
                                <div class="fw-semibold">{{ $lastCheckedAt }}</div>
                            </div>
                        </div>
                        <div class="small">
                            {{ __('Created at') }}: {{ $server->created_at->timezone($timezone)->format('M j, Y H:i') }}<br>
                            {{ __('Updated at') }}: {{ $server->updated_at->timezone($timezone)->format('M j, Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="mb-4">
                <p class="text-uppercase text-muted small mb-1">{{ __('Checks') }}</p>
                <h2 class="h4 mb-0">{{ __('Check settings') }}</h2>
            </div>
            <div class="table-responsive rounded-4 border" style="border-color: var(--app-border-color) !important;">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Check') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Inputs / Thresholds') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($checkSettings as $name => $settings)
                        @php
                            $enabled = data_get($settings, 'enabled', false);
                            $description = data_get($checkDefinitions, "{$name}.description");
                            $inputs = data_get($settings, 'input', []);
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ \Illuminate\Support\Str::headline($name) }}</td>
                            <td>
                                <span class="badge {{ $enabled ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $enabled ? __('Enabled') : __('Disabled') }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $description ?? __('No description available.') }}</td>
                            <td>
                                @if(empty($inputs))
                                    <span class="text-muted small">{{ __('No additional inputs') }}</span>
                                @else
                                    <ul class="list-unstyled mb-0 small">
                                        @foreach($inputs as $key => $value)
                                            @if(is_array($value))
                                                <li><strong>{{ \Illuminate\Support\Str::headline($key) }}:</strong> {{ json_encode($value) }}</li>
                                            @else
                                                <li><strong>{{ \Illuminate\Support\Str::headline($key) }}:</strong> {{ $value === null || $value === '' ? __('Required only') : $value }}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">{{ __('No check settings defined.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer border-top">
            <details>
                <summary class="text-muted small cursor-pointer">{{ __('Show raw JSON') }}</summary>
                <pre class="bg-dark text-white p-3 rounded mt-3 small">{{ json_encode($server->check_settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </details>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="mb-4">
                <p class="text-uppercase text-muted small mb-1">{{ __('History') }}</p>
                <h2 class="h4 mb-0">{{ __('Recent check history') }}</h2>
            </div>
            @php
                $groups = $server->check_histories->groupBy(function ($history) use ($timezone) {
                    return $history->created_at->timezone($timezone)->format('M j, Y H:i:s');
                });
            @endphp
            @if($groups->isEmpty())
                <p class="text-muted mb-0">{{ __('No checks have been recorded yet.') }}</p>
            @else
                @foreach($groups as $timestamp => $entries)
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <span class="fw-semibold">{{ $timestamp }}</span>
                            @php
                                $overallStatus = strtolower(optional($entries->first(fn($item) => $item->name === \App\Jobs\FinalizeServerCheckRun::OVERALL_STATUS))->status ?? 'unknown');
                                $overallBadge = $statusMap[$overallStatus] ?? $statusMap['unknown'];
                            @endphp
                            @if($overallStatus !== 'unknown')
                                <span class="{{ $overallBadge['class'] }} ms-3">{{ $overallBadge['label'] }}</span>
                            @endif
                        </div>
                                <div class="list-group">
                                    @foreach($entries->sortBy('name') as $history)
                                        @continue($history->name === \App\Jobs\FinalizeServerCheckRun::OVERALL_STATUS)
                                        @php
                                            $status = strtolower($history->status ?? 'unknown');
                                            $badge = $statusMap[$status] ?? $statusMap['unknown'];
                                        @endphp
                                        <div class="list-group-item d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="fw-semibold">{{ $history->name }}</div>
                                                <div class="text-muted small">{{ $history->message }}</div>
                                            </div>
                                            <span class="{{ $badge['class'] }}">{{ $badge['label'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</x-app-layout>
