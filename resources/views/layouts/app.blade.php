@php
    $themePreference = optional(auth()->user())->theme_mode ?? 'auto';
    $initialTheme = $themePreference === 'night' ? 'dark' : 'light';
    $initialThemeClass = $initialTheme === 'dark' ? 'theme-night' : 'theme-day';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme-preference="{{ $themePreference }}" data-bs-theme="{{ $initialTheme }}" class="{{ $initialThemeClass }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PHPServerMonitor') }}</title>

        <meta name="description" content="PHPServerMonitor">
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

        <script>
            (() => {
                const root = document.documentElement;
                const preference = root.dataset.themePreference || 'auto';
                if (preference === 'auto' && window.matchMedia) {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    root.dataset.bsTheme = prefersDark ? 'dark' : 'light';
                    root.classList.remove('theme-day', 'theme-night');
                    root.classList.add(prefersDark ? 'theme-night' : 'theme-day');
                }
            })();
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.scss', 'resources/js/app.js'])
    </head>
    <body class="d-flex flex-column min-vh-100">
        @include('layouts.navigation')
        <!-- Page Heading -->
        @isset($header)
            <header class="bg-dark text-white border-bottom pb-3">
                <div class="container-fluid page-header-inner">
                    {{ $header }}
                </div>
            </header>
        @endisset
        <main role="main" class="flex-grow-1 py-4">
            <div class="container-fluid align-items-center justify-content-center">
                {{ $slot }}
            </div>
        </main>
        <footer class="footer mt-auto py-3" role="contentinfo">
            <div class="container-fluid text-center text-md-start">
                <span class="text-body-secondary">
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
