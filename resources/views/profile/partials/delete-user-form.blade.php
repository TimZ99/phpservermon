<section class="card">
    <div class="card-body">
        <p class="text-uppercase text-danger small mb-1">{{ __('Danger zone') }}</p>
        <h2 class="h4 text-danger mb-2">{{ __('Delete Account') }}</h2>
        <p class="text-muted mb-4">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>

        <x-danger-button data-bs-toggle="modal" data-bs-target="#confirm-user-deletion">{{ __('Delete Account') }}</x-danger-button>
    </div>

    <x-modal id="confirm-user-deletion" ariaLabelledby="confirm-user-deletion-label">
        <form method="post" action="{{ route('profile.destroy') }}" class="p-4">
            @csrf
            @method('delete')

            <label for="password" class="form-label">{{ __('Password') }}</label>
            <input id="password"
                class="form-control mb-2"
                type="password"
                name="password"
                required
                autocomplete="current-password" />
            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />

            <div class="modal-header">
                <h5 class="modal-title" id="confirm-user-deletion-label">{{ __('Are you sure you want to delete your account?') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}</p>
            </div>
            <div class="modal-footer">
                <x-secondary-button type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</x-secondary-button>
                <x-danger-button class="ms-3">{{ __('Delete Account') }}</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
