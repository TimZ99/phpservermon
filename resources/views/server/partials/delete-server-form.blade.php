<section>
    <header class="mt-4">
        <h2>{{ __('Delete server') }} </h2>
        <p>{{ __('Once a server is deleted, all of its resources and data will be permanently deleted.') }}</p>
    </header>

    <x-danger-button data-bs-toggle="modal" data-bs-target="#confirm-server-deletion">{{ __('Delete server') }}</x-danger-button>

    <x-modal id="confirm-server-deletion">
        <form method="post" action="{{ route('server.destroy', $server->id) }}" class="p-6">
            @csrf
            @method('delete')
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Are you sure you want to delete the server?') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Once a server is deleted, all of its resources and data will be permanently deleted.') }}</p>
            </div>
            <div class="modal-footer">
                <x-secondary-button type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</x-secondary-button>
                <x-danger-button class="ms-3">{{ __('Delete server') }}</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
