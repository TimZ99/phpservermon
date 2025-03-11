<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand ps-2" href="#">PHPServerMonitor</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            @auth
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <x-nav-link :href="route('server.monitor')" :active="request()->routeIs('server.monitor')">
                        {{ __('Monitor') }}
                    </x-nav-link>
                    <x-nav-link :href="route('server.index')" :active="request()->routeIs('server.index')">
                        {{ __('Servers') }}
                    </x-nav-link>
                    <x-nav-link :href="route('user.index')" :active="request()->routeIs('user.index')">
                        {{ __('Users') }}
                    </x-nav-link>
                    @can('admin-only')
                    <x-nav-link :href="route('config.edit')" :active="request()->routeIs('config.edit')">
                        {{ __('Config') }}
                    </x-nav-link>
                    @endcan
                </ul>
                <li class="nav-item dropdown d-flex pe-4 text-bg-dark">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <li><a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</a>
                            </li>
                        </form>
                    </ul>
                </li>
            @else
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    @if (Route::has('login'))
                        <x-nav-link :href="route('login')" class="d-flex pe-4" :active="request()->routeIs('login')">
                            {{ __('Login') }}
                        </x-nav-link>
                    @endif
                    @if (Route::has('register'))
                        <x-nav-link :href="route('register')" class="d-flex pe-4" :active="request()->routeIs('register')">
                            {{ __('Register') }}
                        </x-nav-link>
                    @endif
                </ul>
            @endauth
        </div>
    </div>
</nav>