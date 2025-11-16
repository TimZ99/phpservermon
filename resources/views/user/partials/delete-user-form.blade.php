<section class="card">
    <div class="card-body">
        <p class="text-uppercase text-danger small mb-1">{{ __('Danger zone') }}</p>
        <h2 class="h4 text-danger mb-2">{{ __('Delete user') }}</h2>
        <p class="text-body-secondary mb-4">{{ __('Once a user is deleted, all of its resources and data will be permanently deleted.') }}</p>

        <x-danger-button data-bs-toggle="modal" data-bs-target="#confirm-user-deletion">{{ __('Delete user') }}</x-danger-button>
        <x-input-error class="mt-2" :messages="$errors->get('user:editdelete')" />
    </div>

    <x-modal id="confirm-user-deletion" ariaLabelledby="confirm-user-deletion-label">
        <form method="post" action="{{ route('user.destroy', $user->id) }}" class="p-4">
            @csrf
            @method('delete')
            <div class="modal-header">
                <h5 class="modal-title" id="confirm-user-deletion-label">{{ __('Are you sure you want to delete the user?') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Once a user is deleted, all of its resources and data will be permanently deleted.') }}</p>
            </div>
            <div class="modal-footer">
                <x-secondary-button type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</x-secondary-button>
                <x-danger-button class="ms-3">{{ __('Delete user') }}</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
