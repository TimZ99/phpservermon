<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PHPServerMonitor') }}</title>

        <meta name="description" content="PHP Server Monitor">
        <meta name="robots" content="noindex" />
        <!--<link rel="manifest" href="./manifest.json">-->
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="apple-mobile-web-app-title" content="PSM">
        <link rel="apple-touch-icon" href="/phpservermon.png">
        <meta name="msapplication-TileImage" content="./phpservermon.png">
        <meta name="msapplication-TileColor" content="#424242">

        <meta name="theme-color" content="#424242">
        <link rel="icon" type="image/x-icon" href="/favicon.ico" />
        <link rel="icon" type="image/png" href="/favicon.png" />
        <link rel="apple-touch-icon" href="/favicon.png" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.scss', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <nav class="navbar navbar-expand-lg bg-light">
            <div class="container-fluid">
                <a class="navbar-brand ps-2" href="#">PHPServerMonitor</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                    <a class="nav-link @if(Route::currentRouteName() == 'dashboard')active @endif" aria-current="page" href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                    <a class="nav-link @if(Route::currentRouteName() == 'server.index')active @endif" aria-current="page" href="{{ route('server.index') }}">Servers</a>
                    </li>
                </ul>
                <li class="nav-item dropdown d-flex pe-4">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Profile
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <form method="POST" action="{{ route('logout') }}" >
                            @csrf
                            <li><a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</a></li>
                        </form> 
                    </ul>
                    </li>
                </div>
            </div>
        </nav>
        <main role="main" class="container-fluid px-4">
            <!-- Page Heading -->
            @isset($header)
                <header>
                    {{ $header }}
                </header>
            @endisset
            {{ $slot }}
        </main>
        <footer class="footer fixed-bottom" role="contentinfo">
            <div class="container">
                <span class="text-muted">
                    Powered by
                    <a href="https://github.com/phpservermon/phpservermon/" target="_blank" rel="noopener">
                        PHPServerMonitor.
                    </a>
                    Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
                </span>
            </div>
        </footer>
    </body>
</html>
