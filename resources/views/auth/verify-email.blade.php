<x-guest-layout>
    <div class="container min-vh-100 py-5 d-flex align-items-center justify-content-center">
        <div class="card w-100" style="max-width: 520px;">
            <div class="card-body">
                <div class="mb-4">
                    <p class="text-uppercase text-muted small mb-1">{{ __('Verification') }}</p>
                    <h1 class="h5 mb-0">{{ __('Verify email address') }}</h1>
                </div>

                <p class="card-text mb-4">
                    {{ __("Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.") }}
                </p>

                @if (session('status') == 'verification-link-sent')
                    <p class="alert alert-success" role="alert">
                        {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                    </p>
                @endif

                <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
                    @csrf
                    <div class="d-flex justify-content-end">
                        <x-primary-button outline>
                            {{ __('Resend Verification Email') }}
                        </x-primary-button>
                    </div>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <div class="d-flex justify-content-end">
                        <x-primary-button>
                            {{ __('Log out') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
