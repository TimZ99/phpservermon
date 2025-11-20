<section class="card">
    <div class="card-body">
        <div class="mb-4">
            <p class="text-uppercase text-muted small mb-1">{{ __('Security') }}</p>
            <h2 class="h4 mb-1">{{ __('Passkey Authentication') }}</h2>
            <p class="text-muted mb-0">
                {{ __('Register a passkey to sign in without a password. Once at least one passkey is active, email/password sign in is disabled until you remove every passkey.') }}
            </p>
        </div>

        <div class="d-flex flex-column gap-4" x-data>
            <form class="row g-3 align-items-end" data-passkey-form
                data-options-url="{{ route('passkeys.options') }}"
                data-store-url="{{ route('passkeys.store') }}">
                <div class="col-12 col-md">
                    <label for="passkey-name" class="form-label">{{ __('passkeys::passkeys.name') }}</label>
                    <input type="text" id="passkey-name" name="passkey_name" class="form-control" autocomplete="off" required />
                </div>
                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-primary px-4" data-passkey-submit>
                        {{ __('passkeys::passkeys.create') }}
                    </button>
                </div>
            </form>

            <div id="passkey-status" class="alert d-none mb-0" role="alert"></div>

            @if(($user->passkeys ?? collect())->isEmpty())
                <div class="alert alert-secondary text-bg-secondary mb-0">
                    {{ __('passkeys::passkeys.not_used_yet') }}
                </div>
            @else
                <ul class="list-group">
                    @foreach($user->passkeys as $passkey)
                        <li class="list-group-item d-flex flex-column flex-lg-row gap-3 justify-content-between align-items-lg-center">
                            <div>
                                <div class="fw-semibold">{{ $passkey->name }}</div>
                                <div class="text-muted small">
                                    {{ __('passkeys::passkeys.last_used') }}:
                                    {{ $passkey->last_used_at?->diffForHumans() ?? __('passkeys::passkeys.not_used_yet') }}
                                </div>
                            </div>
                            <form method="POST" action="{{ route('passkeys.destroy', $passkey) }}" class="ms-lg-auto">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm" type="submit">
                                    {{ __('passkeys::passkeys.delete') }}
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif

            <p class="text-muted small mb-0">
                {{ __('Need to go back to passwords? Remove your saved passkeys and the classic email/password flow becomes available again.') }}
            </p>
        </div>
    </div>
</section>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('[data-passkey-form]');
        const statusEl = document.getElementById('passkey-status');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        if (!form || typeof window.startRegistration !== 'function') {
            return;
        }

        const setStatus = (message, variant = 'success') => {
            if (!statusEl) {
                return;
            }

            if (!message) {
                statusEl.classList.add('d-none');

                return;
            }

            statusEl.textContent = message;
            statusEl.classList.remove('d-none', 'alert-success', 'alert-danger');
            statusEl.classList.add(variant === 'success' ? 'alert-success' : 'alert-danger');
        };

        const postJson = async (url, payload) => {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ?? '',
                },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                const message = data?.message
                    ?? Object.values(data?.errors ?? {})[0]?.[0]
                    ?? response.statusText;

                throw new Error(message);
            }

            return response.json();
        };

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const nameField = form.querySelector('[name="passkey_name"]');
            const submitButton = form.querySelector('[data-passkey-submit]');

            submitButton?.setAttribute('disabled', 'disabled');
            setStatus('');

            try {
                const { options } = await postJson(form.dataset.optionsUrl, {
                    name: nameField.value.trim(),
                });

                const optionsJSON = typeof options === 'string' ? JSON.parse(options) : options;

                const passkey = await window.startRegistration({ optionsJSON });

                await postJson(form.dataset.storeUrl, {
                    passkey: JSON.stringify(passkey),
                });

                window.location.reload();
            } catch (error) {
                setStatus(error?.message ?? error, 'danger');
            } finally {
                submitButton?.removeAttribute('disabled');
            }
        });
    });
</script>