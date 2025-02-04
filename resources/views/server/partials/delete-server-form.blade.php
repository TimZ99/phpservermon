<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Delete server') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Once a server is deleted, all of its resources and data will be permanently deleted.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-server-deletion')"
    >{{ __('Delete server') }}</x-danger-button>

    <x-modal name="confirm-server-deletion" :show="$errors->serverDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('server.destroy', $server->id) }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete the server?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Once a server is deleted, all of its resources and data will be permanently deleted.') }}
            </p>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete server') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
