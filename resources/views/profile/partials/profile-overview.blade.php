@php
    $displayName = trim($user->name ?? $user->email);
    $nameParts = preg_split('/\s+/', $displayName) ?: [];
    $initials = '';

    foreach ($nameParts as $part) {
        if ($part === '') {
            continue;
        }
        $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        if (mb_strlen($initials) >= 2) {
            break;
        }
    }

    if ($initials === '' && $user->email) {
        $initials = mb_strtoupper(mb_substr($user->email, 0, 1));
    }

    $emailNeedsVerification = $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail();
    $telegramReady = $telegramGloballyEnabled && $telegramBotConfigured;
    $hasTelegramUser = $telegramReady && ! empty($user->telegram_user_id);
@endphp

<section class="card">
    <div class="card-body">
        <div class="text-center">
            <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-semibold d-inline-flex align-items-center justify-content-center"
                 style="width: 72px; height: 72px;">
                {{ $initials }}
            </div>
            <h2 class="h5 mt-3 mb-1">{{ $user->name ?? __('Unnamed User') }}</h2>
            <p class="text-muted mb-0">{{ $user->email }}</p>
        </div>

        <div class="mt-4 pt-3 border-top">
            <dl class="row small mb-0">
                <dt class="col-6 text-muted">{{ __('Email status') }}</dt>
                <dd class="col-6 mb-2 text-end">
                    @if($emailNeedsVerification)
                        <span class="badge bg-warning-subtle text-warning-emphasis">{{ __('Unverified') }}</span>
                    @else
                        <span class="badge bg-success-subtle text-success-emphasis">{{ __('Verified') }}</span>
                    @endif
                </dd>

                <dt class="col-6 text-muted">{{ __('Phone') }}</dt>
                <dd class="col-6 mb-2 text-end">
                    {{ $user->phone ?: __('Not set') }}
                </dd>

                <dt class="col-6 text-muted">{{ __('Telegram') }}</dt>
                <dd class="col-6 mb-2 text-end">
                    @if(! $telegramReady)
                        <span class="badge bg-secondary-subtle text-secondary-emphasis">{{ __('Disabled') }}</span>
                    @elseif($hasTelegramUser)
                        <span class="badge bg-success-subtle text-success-emphasis">{{ __('Connected') }}</span>
                    @else
                        <span class="badge bg-warning-subtle text-warning-emphasis">{{ __('Not linked') }}</span>
                    @endif
                </dd>

                <dt class="col-6 text-muted">{{ __('Member since') }}</dt>
                <dd class="col-6 text-end">
                    {{ optional($user->created_at)->format('M j, Y') }}
                </dd>
            </dl>
        </div>
    </div>
</section>
