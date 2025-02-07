<x-guest-layout>
    <div class="mt-4 col-12 col-md-9 col-lg-7 col-xl-6 col-xxl-5 container d-flex align-items-center justify-content-center">
        <div class="card">
            <div class="card-header">
                Verify email address
            </div>
            <div class="card-body">
                <p class="card-text mb-4">
                Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.'
                </p>

                @if (session('status') == 'verification-link-sent')
                    <p class="mb-4 text-success card-text">
                        A new verification link has been sent to the email address you provided during registration.
                    </p>
                @endif

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button outline>
                            {{ __('Resend Verification Email') }}
                        </x-primary-button>
                    </div>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button>
                            {{ __('Log out') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
