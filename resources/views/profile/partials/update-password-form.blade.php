<section class="card shadow-sm border-0">
    <div class="card-body">
        <div class="mb-4">
            <p class="text-uppercase text-muted small mb-1">{{ __('Security') }}</p>
            <h2 class="h4 mb-1">{{ __('Update Password') }}</h2>
            <p class="text-muted mb-0">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
        </div>

        <form method="post" action="{{ route('password.update') }}" class="row g-3">
            @csrf
            @method('put')

            <div class="col-12">
                <label for="update_password_current_password" class="form-label">{{ __('Current Password') }}</label>
                <input id="update_password_current_password" name="current_password" type="password" class="form-control" autocomplete="current-password" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>

            <div class="col-12 col-md-6">
                <label for="update_password_password" class="form-label">{{ __('New Password') }}</label>
                <input id="update_password_password" name="password" type="password" class="form-control" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            </div>

            <div class="col-12 col-md-6">
                <label for="update_password_password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="col-12 d-flex flex-wrap gap-3 align-items-center">
                <x-primary-button>{{ __('Save password') }}</x-primary-button>

                @if (session('status') === 'password-updated')
                    <span
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 2000)"
                        class="text-success small fw-semibold"
                    >{{ __('Saved') }}</span>
                @endif
            </div>
        </form>
    </div>
</section>
