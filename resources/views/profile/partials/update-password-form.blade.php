<div class="mb-4">
    <h2 class="h4 mb-1">{{ __('Update Password') }}</h2>
    <p class="text-muted mb-0">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
</div>
<div class="d-flex flex-column gap-4">
    @if (session('status') === 'password-updated')
        <div class="alert alert-success text-bg-success mb-0">
            {{ __('Your password has been updated successfully.') }}
        </div>
    @endif
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
        </div>
    </form>
</div>
